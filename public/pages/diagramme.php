<?php

use Src\Classes\Project\Icon;

/**
 * Ein Diagramm-Blatt: Kopf mit Titel, Einheit und Kennzahl, darunter die Zeichenfläche.
 * Kennzahl und Untertitel rechts füllt diagramme.ts (#<id>Total, #<id>TotalSub).
 */
$chartSheet = function (string $id, string $title, string $unit, string $height = "h-64"): string {
    return <<<HTML
        <div class="sheet min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 pb-2.5 mb-2.5 border-b border-line-soft">
                <div>
                    <h3 class="font-heading font-bold text-[0.9375rem] text-ink">{$title}</h3>
                    <p class="text-xs text-text-muted">{$unit}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold tabular-nums" id="{$id}Total">–</p>
                    <p class="text-xs text-text-muted" id="{$id}TotalSub"></p>
                </div>
            </div>
            <div class="relative {$height}">
                <canvas id="{$id}"></canvas>
            </div>
        </div>
    HTML;
};

?>
<div class="mx-2.5 mt-2 mb-4 flex flex-wrap items-end justify-between gap-3">
	<div>
		<span class="group-label text-text-muted">Auswertungen</span>
		<h2 class="page-title" id="rangeTitle">Letzte 12 Monate</h2>
		<p class="text-xs text-text-muted" id="rangeSubtitle"></p>
	</div>
	<div class="flex flex-wrap items-center gap-2">
		<div class="btn-group" role="group" aria-label="Zeitraum">
			<button type="button" data-fun="presetRange30" data-binding="true" data-preset="30d">30 Tage</button>
			<button type="button" data-fun="presetRange90" data-binding="true" data-preset="90d">90 Tage</button>
			<button type="button" data-fun="presetRange12Months" data-binding="true" data-preset="12m">12 Monate</button>
			<button type="button" data-fun="presetRangeYear" data-binding="true" data-preset="year">Dieses Jahr</button>
		</div>
		<button type="button" class="btn-secondary btn-sm" data-toggle="true" data-target="#customRange" id="customRangeToggle">
			Eigener Zeitraum <?= Icon::get("chevron-down", 14) ?>
		</button>
	</div>
</div>

<div class="hidden mx-2.5 mb-3" id="customRange">
	<div class="sheet flex flex-wrap items-end gap-3">
		<label class="field">
			<span class="field-label">Startdatum</span>
			<input type="date" id="startDate" class="input-primary">
		</label>
		<label class="field">
			<span class="field-label">Enddatum</span>
			<input type="date" id="endDate" class="input-primary">
		</label>
		<button class="btn-primary" data-fun="applyRange" data-binding="true">Anwenden</button>
	</div>
</div>

<div id="legacyCutoffNotice" class="hidden note note-info mx-2.5 mb-3">
	<p class="note-head"><?= Icon::get("info") ?>Hinweis</p>
	<p id="legacyCutoffText"></p>
</div>

<div class="mx-2.5 mb-2.5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 bg-sheet border border-line-soft rounded-control overflow-hidden">
	<div class="kpi kpi-main">
		<span class="kpi-label">Umsatz netto</span>
		<span class="kpi-value" id="kpiRevenue">–</span>
		<span class="kpi-delta" id="kpiRevenueDelta"></span>
	</div>
	<div class="kpi">
		<span class="kpi-label">Aufträge</span>
		<span class="kpi-value" id="kpiOrderCount">–</span>
		<span class="kpi-delta" id="kpiOrderCountDelta"></span>
	</div>
	<div class="kpi">
		<span class="kpi-label">Ø Zahlungsdauer</span>
		<span class="kpi-value" id="kpiPaymentDuration">–</span>
		<span class="kpi-delta" id="kpiPaymentDurationDelta"></span>
	</div>
	<div class="kpi">
		<span class="kpi-label">Offene Rechnungen</span>
		<span class="kpi-value" id="kpiOpenInvoices">–</span>
		<span class="kpi-delta" id="kpiOpenInvoicesDetail"></span>
	</div>
</div>

<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Umsatz und Auftragseingang</h2>
		<span class="section-meta">Umsatz netto nach Rechnungsdatum, Gutschriften im Monat ihrer Ausstellung</span>
	</div>
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
		<?= $chartSheet("chartRevenue", "Umsatz pro Monat", "in €") ?>
		<?= $chartSheet("chartOrders", "Auftragseingang pro Monat", "Anzahl neuer Aufträge") ?>
	</div>
</section>

<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Offene Posten und Zahlungen</h2>
		<span class="section-meta">Alter offener Rechnungen gerechnet ab Rechnungsdatum bis heute</span>
	</div>
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
		<?= $chartSheet("chartAging", "Offene Rechnungen nach Alter", "Betrag in €") ?>
		<?= $chartSheet("chartPaymentDuration", "Zahlungsdauer pro Monat", "Ø Tage von Rechnung bis Zahlung") ?>
	</div>
</section>

<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Aufträge und Kunden</h2>
		<span class="section-meta">Aufträge mit Eingang im Zeitraum, Kunden nach Umsatz im Zeitraum</span>
	</div>
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
		<?= $chartSheet("chartPipeline", "Auftragsstatus", "Anzahl Aufträge") ?>
		<?= $chartSheet("chartTopCustomers", "Top-Kunden", "Umsatz netto in €", "h-80") ?>
	</div>
</section>
