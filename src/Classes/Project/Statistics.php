<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Statistics
{
    /** @var array<string, string> */
    private const ORDER_STATE_LABELS = [
        "default" => "Offen",
        "finished" => "Fertiggestellt",
        "archived" => "Archiviert",
        "invoiced" => "Abgerechnet",
    ];

    /** @var array<int, array{key: string, label: string, minDays: int, maxDays: ?int}> */
    private const AGING_BUCKETS = [
        ["key" => "0-30", "label" => "0-30 Tage", "minDays" => 0, "maxDays" => 30],
        ["key" => "31-60", "label" => "31-60 Tage", "minDays" => 31, "maxDays" => 60],
        ["key" => "61-90", "label" => "61-90 Tage", "minDays" => 61, "maxDays" => 90],
        ["key" => "90+", "label" => "Über 90 Tage", "minDays" => 91, "maxDays" => null],
    ];

    /**
     * @return array<int, array<string, string>>
     */
    public static function getOrdersOverTime(string $startDate, string $endDate): array
    {
        $query = "SELECT DATE_FORMAT(Datum, '%Y-%m') AS `date`, COUNT(*) AS `value`
			FROM auftrag
			WHERE Datum BETWEEN :startDate AND :endDate
			GROUP BY DATE_FORMAT(Datum, '%Y-%m')
			ORDER BY `date`";

        return DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    /**
     * status = 'finalized' is the authoritative "this counts as real revenue" flag, not finalized_date: a 2025
     * bulk migration (2025-04-16_invoiceNumber.php) set status = 'finalized' on all pre-existing invoices without
     * backfilling finalized_date (that column didn't exist yet), so most historical invoices have the status but
     * no finalized_date. creation_date is always populated, so it's the fallback for bucketing those.
     *
     * @return array<int, array<string, string>>
     */
    public static function getRevenueOverTime(string $startDate, string $endDate): array
    {
        $query = "SELECT DATE_FORMAT(COALESCE(finalized_date, creation_date), '%Y-%m') AS `date`, ROUND(SUM(amount), 2) AS `value`
			FROM invoice
			WHERE `status` = 'finalized'
				AND COALESCE(finalized_date, creation_date) BETWEEN :startDate AND :endDate
			GROUP BY DATE_FORMAT(COALESCE(finalized_date, creation_date), '%Y-%m')
			ORDER BY `date`";

        return DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    /**
     * Duration can only be computed for invoices where both a start reference (finalized_date, falling back to
     * creation_date - see getRevenueOverTime()) and a real payment_date exist. Legacy rows may carry the invalid
     * MySQL zero-date '0000-00-00' instead of NULL for "not paid yet" (see other '0000-00-00' checks in Auftrag.php),
     * so that has to be excluded explicitly, not just NULL.
     *
     * Sample size is small for older data: payment_date was rarely recorded until invoice reminders/payment
     * tracking were built out, so most paid-long-ago invoices only have auftrag.Bezahlt = 1 with no payment_date
     * (see getOpenInvoiceAging()). Where an old invoice was only marked paid recently via the UI, payment_date
     * reflects that catch-up date, not the true historical payment date - the number is honest given the data,
     * but not representative until more invoices get a real payment_date going forward.
     *
     * @return array<int, array<string, string>>
     */
    public static function getPaymentDurationOverTime(string $startDate, string $endDate): array
    {
        $query = "SELECT DATE_FORMAT(payment_date, '%Y-%m') AS `date`, ROUND(AVG(DATEDIFF(payment_date, COALESCE(finalized_date, creation_date))), 1) AS `value`
			FROM invoice
			WHERE `status` = 'finalized'
				AND payment_date IS NOT NULL
				AND payment_date != '0000-00-00'
				AND payment_date BETWEEN :startDate AND :endDate
			GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
			ORDER BY `date`";

        return DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    /**
     * @return array{avgDays: ?float, count: int}
     */
    public static function getAveragePaymentDuration(string $startDate, string $endDate): array
    {
        $query = "SELECT ROUND(AVG(DATEDIFF(payment_date, COALESCE(finalized_date, creation_date))), 1) AS avgDays, COUNT(*) AS `count`
			FROM invoice
			WHERE `status` = 'finalized'
				AND payment_date IS NOT NULL
				AND payment_date != '0000-00-00'
				AND payment_date BETWEEN :startDate AND :endDate";

        $result = DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);

        $avgDays = $result[0]["avgDays"] ?? null;

        return [
            "avgDays" => is_numeric($avgDays) ? (float) $avgDays : null,
            "count" => (int) ($result[0]["count"] ?? 0),
        ];
    }

    /**
     * "Is this paid?" is auftrag.Bezahlt here, not invoice.payment_date - that's what InvoiceHelper::getOpenInvoiceData()
     * (the actual /offeneRechnungen page) uses as the source of truth, and the two disagree for ~300 historical
     * invoices that were marked paid via Bezahlt long ago but never got a payment_date backfilled. Using
     * payment_date here would resurrect already-settled invoices as "open".
     *
     * Restricted to invoices finalized in the selected range (see getRevenueOverTime() for the status/date-fallback
     * reasoning); the age itself is always "today minus the finalized/creation date", since a still-open invoice's
     * age can only be measured as of now.
     *
     * @return array<int, array{bucket: string, label: string, count: int, amount: float}>
     */
    public static function getOpenInvoiceAging(string $startDate, string $endDate): array
    {
        $query = "SELECT DATEDIFF(CURDATE(), COALESCE(invoice.finalized_date, invoice.creation_date)) AS daysOpen, invoice.amount
			FROM invoice
			INNER JOIN auftrag a ON a.Auftragsnummer = invoice.order_id
			WHERE invoice.status = 'finalized'
				AND a.Bezahlt = 0
				AND COALESCE(invoice.finalized_date, invoice.creation_date) BETWEEN :startDate AND :endDate";

        $rows = DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);

        $buckets = [];
        foreach (self::AGING_BUCKETS as $bucket) {
            $buckets[$bucket["key"]] = [
                "bucket" => $bucket["key"],
                "label" => $bucket["label"],
                "count" => 0,
                "amount" => 0.0,
            ];
        }

        foreach ($rows as $row) {
            $daysOpen = (int) $row["daysOpen"];
            foreach (self::AGING_BUCKETS as $bucket) {
                if ($daysOpen >= $bucket["minDays"] && ($bucket["maxDays"] === null || $daysOpen <= $bucket["maxDays"])) {
                    $buckets[$bucket["key"]]["count"]++;
                    $buckets[$bucket["key"]]["amount"] += (float) $row["amount"];
                    break;
                }
            }
        }

        foreach ($buckets as &$bucket) {
            $bucket["amount"] = round($bucket["amount"], 2);
        }

        return array_values($buckets);
    }

    /**
     * @return array<int, array{status: string, label: string, value: int}>
     */
    public static function getOrderPipeline(string $startDate, string $endDate): array
    {
        $query = "SELECT `status`, COUNT(*) AS `value`
			FROM auftrag
			WHERE Datum BETWEEN :startDate AND :endDate
			GROUP BY `status`";

        $rows = DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);

        $pipeline = [];
        foreach (self::ORDER_STATE_LABELS as $status => $label) {
            $pipeline[$status] = [
                "status" => $status,
                "label" => $label,
                "value" => 0,
            ];
        }

        foreach ($rows as $row) {
            $status = $row["status"];
            if (!isset($pipeline[$status])) {
                continue;
            }
            $pipeline[$status]["value"] = (int) $row["value"];
        }

        return array_values($pipeline);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getTopCustomers(string $startDate, string $endDate, int $limit = 10): array
    {
        $query = "SELECT
				IF(kunde.Firmenname = '', TRIM(CONCAT(kunde.Vorname, ' ', kunde.Nachname)), kunde.Firmenname) AS name,
				ROUND(SUM(invoice.amount), 2) AS `value`,
				COUNT(DISTINCT invoice.id) AS orderCount
			FROM invoice
			INNER JOIN auftrag a ON invoice.order_id = a.Auftragsnummer
			INNER JOIN kunde ON kunde.Kundennummer = a.Kundennummer
			WHERE invoice.status = 'finalized'
				AND COALESCE(invoice.finalized_date, invoice.creation_date) BETWEEN :startDate AND :endDate
			GROUP BY kunde.Kundennummer, name
			ORDER BY `value` DESC
			LIMIT :limit";

        return DBAccess::selectQuery($query, [
            "startDate" => $startDate,
            "endDate" => $endDate,
            "limit" => $limit,
        ]);
    }

    /**
     * Optional global setting for installs carrying pre-migration invoice data from a different, older invoicing
     * system (unset by default, so a fresh install behaves exactly as if this didn't exist). Invoice-derived
     * metrics clamp their start date to this cutoff instead of hard-excluding older rows outright, so a range
     * that starts before it just narrows automatically rather than silently returning nothing.
     */
    private static function clampToLegacyCutoff(string $startDate): string
    {
        $cutoff = (string) Settings::get("statistics.legacyDataCutoff");

        if ($cutoff === "" || !validateDateString($cutoff, "Y-m-d")) {
            return $startDate;
        }

        return max($startDate, $cutoff);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getDashboard(string $startDate, string $endDate): array
    {
        $financeStartDate = self::clampToLegacyCutoff($startDate);

        $ordersOverTime = self::getOrdersOverTime($startDate, $endDate);
        $revenueOverTime = self::getRevenueOverTime($financeStartDate, $endDate);
        $paymentDuration = self::getAveragePaymentDuration($financeStartDate, $endDate);
        $openInvoiceAging = self::getOpenInvoiceAging($financeStartDate, $endDate);

        $orderCount = array_sum(array_column($ordersOverTime, "value"));
        $revenue = array_sum(array_column($revenueOverTime, "value"));
        $openInvoiceCount = array_sum(array_column($openInvoiceAging, "count"));
        $openInvoiceAmount = array_sum(array_column($openInvoiceAging, "amount"));

        return [
            "range" => [
                "startDate" => $startDate,
                "endDate" => $endDate,
            ],
            "legacyDataCutoff" => $financeStartDate !== $startDate ? $financeStartDate : null,
            "kpis" => [
                "orderCount" => (int) $orderCount,
                "revenue" => round((float) $revenue, 2),
                "avgPaymentDuration" => $paymentDuration["avgDays"],
                "avgPaymentDurationSampleSize" => $paymentDuration["count"],
                "openInvoiceCount" => (int) $openInvoiceCount,
                "openInvoiceAmount" => round((float) $openInvoiceAmount, 2),
            ],
            "ordersOverTime" => $ordersOverTime,
            "revenueOverTime" => $revenueOverTime,
            "paymentDurationOverTime" => self::getPaymentDurationOverTime($financeStartDate, $endDate),
            "orderPipeline" => self::getOrderPipeline($startDate, $endDate),
            "openInvoiceAging" => $openInvoiceAging,
            "topCustomers" => self::getTopCustomers($financeStartDate, $endDate),
        ];
    }

    public static function getDashboardAjax(): void
    {
        $startDate = (string) Tools::get("startDate");
        $endDate = (string) Tools::get("endDate");

        if (!validateDateString($startDate, "Y-m-d")) {
            $startDate = date("Y-m-d", strtotime("-12 months"));
        }
        if (!validateDateString($endDate, "Y-m-d")) {
            $endDate = date("Y-m-d");
        }

        JSONResponseHandler::sendResponse(self::getDashboard($startDate, $endDate));
    }
}
