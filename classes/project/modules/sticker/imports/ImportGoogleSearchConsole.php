<?php

require_once 'vendor/autoload.php';

use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;

class ImportGoogleSearchConsole {

    private $searchConsole;

    function __construct() {
        $keyFileLocation = $_ENV["GOOGLE_SEARCHCONSOLE"];
        $client = new Google\Client();
        try {
            $client->setAuthConfig($keyFileLocation);
        } catch (Exception $e) {
            echo "Fehler bei der Authentifizierung";
            Protocol::write("Google Search Console", "Error authenticating: " . $e->getMessage());
            return;
        }
        $client->setApplicationName("Search Console Request");
        $client->addScope("https://www.googleapis.com/auth/webmasters.readonly");
        $client->addScope("https://www.googleapis.com/auth/webmasters");
        $this->searchConsole = new SearchConsole($client);
    }

    public function getStats($url) {
        $queryRequest = new SearchAnalyticsQueryRequest();
        $queryRequest->setStartDate("2023-10-01");
        $queryRequest->setEndDate("2023-10-31");

        try {
            $response = $this->searchConsole->searchanalytics->query($url, $queryRequest);
        } catch (Exception $e) {
            $this->addUrl($url);
            echo "Fehler bei der Abfrage";
            Protocol::write("Google Search Console", "Error querying: " . $e->getMessage());
            return;
        }
        
        $rows = $response->getRows();

        if (is_null($rows)) {
            echo "Keine Daten gefunden";
        } else {
            foreach ($rows as $row) {
                $html = "<p>Clicks: " . $row->getClicks() . "</p>";
                $html .= "<p>Impressions: " . $row->getImpressions() . "</p>";
                $html .= "<p>CTR: " . $row->getCTR() . "</p>";
                $html .= "<p>Position: " . $row->getPosition() . "</p>";
                echo $html;
            }
        }
    }

    private function addUrl($url) {
        $data = $this->searchConsole->sites->add($url);
        var_dump($data);
    }

    public function import() {

    }
    
}
