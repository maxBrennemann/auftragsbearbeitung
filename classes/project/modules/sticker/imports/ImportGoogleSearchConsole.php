<?php

class ImportGoogleSearchConsole {

    public static function getStats($url) {
        $key = GOOGLE_SEARCHCONSOLE;
        $apiUrl = "https://searchconsole.googleapis.com/v1/urlInspection/index:inspect?key=$key";

        $client = new \GuzzleHttp\Client();
        $client->request('POST', $apiUrl, [
            GuzzleHttp\RequestOptions::JSON => [
                "inspectUrl" => $url,
                "siteUrl" => "https://klebefux.de/",
                "languageCode" => "de-DE",
            ] 
        ]);
    }

}
