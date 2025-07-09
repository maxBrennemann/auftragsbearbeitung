<?php

namespace Classes\Sticker\Imports;

use Classes\Protocol;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use MaxBrennemann\PhpUtilities\DBAccess;

class ImportGoogleSearchConsole
{
    private $searchConsole;

    public function __construct()
    {
        $keyFileLocation = $_ENV["GOOGLE_SEARCHCONSOLE"];
        $client = new \Google\Client();
        try {
            $client->setAuthConfig($keyFileLocation);
        } catch (\Exception $e) {
            echo "Fehler bei der Authentifizierung";
            Protocol::write("Google Search Console", "Error authenticating: " . $e->getMessage());
            return;
        }
        $client->setApplicationName("Search Console Request");
        $client->addScope("https://www.googleapis.com/auth/webmasters.readonly");
        $client->addScope("https://www.googleapis.com/auth/webmasters");
        $this->searchConsole = new SearchConsole($client);
    }

    public function getStats($url)
    {
        $queryRequest = new SearchAnalyticsQueryRequest();
        $queryRequest->setStartDate(date('Y-m-d', strtotime('-7 days')));
        $queryRequest->setEndDate(date('Y-m-d', strtotime('-3 days')));
        $queryRequest->setDimensions(['page', 'date']);
        $queryRequest->setAggregationType("byPage");

        try {
            $response = $this->searchConsole->searchanalytics->query($url, $queryRequest);
        } catch (\Exception $e) {
            Protocol::write("Google Search Console", "Error querying: " . $e->getMessage());
            return;
        }

        $rows = $response->getRows();

        if (is_null($rows)) {
            Protocol::write("Google Search Console", "No data available");
            return;
        } else {
            $completeData = [];
            foreach ($rows as $row) {
                $data = [];
                $data[] = $row->getClicks();
                $data[] = $row->getImpressions();
                $data[] = $row->getCTR();
                $data[] = $row->getPosition();
                $data[] = $row->keys[0];
                $data[] = $row->keys[1];

                $completeData[] = $data;
            }

            DBAccess::insertMultiple("INSERT INTO module_sticker_search_data (clicks, impressions, ctr, position, `site`, `date`) VALUES ", $completeData);
        }
    }

    private function addUrl($url)
    {
        $data = $this->searchConsole->sites->add($url);
        var_dump($data);
    }

    public static function import()
    {
        $import = new ImportGoogleSearchConsole();
        $import->getStats("https://klebefux.de/");
    }

    /**
     * gets all search data for a given url,
     * if no start and end date are given, the last 7 days are used
     */
    public static function get($url, ?string $startDate = null, ?string $endDate = null)
    {
        if ($startDate == null) {
            $startDate = date('Y-m-d', strtotime('-7 days'));
        }
        if ($endDate == null) {
            $endDate = date('Y-m-d', strtotime('-3 days'));
        }

        $query = "SELECT * FROM module_sticker_search_data WHERE `site`= :url AND `date` >= :startDate AND `date` <= :endDate;";
        $data = DBAccess::selectQuery($query, [
            "url" => $url,
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
        return $data;
    }
}
