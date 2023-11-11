<?php

require_once 'vendor/autoload.php';

class ImportGoogleSearchConsole {

    public static function getStats($url) {
        $key = $_ENV["GOOGLE_SEARCHCONSOLE"];
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

    private static function authenticate() {
        $client = new Google\Client();
        $client->setApplicationName("Auftragsbearbeitung");
        $client->setAuthConfig('/config/google-api.json');
        $client->setScopes([
            Google\Service\SearchConsole::WEBMASTERS,
        ]);

        $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
        $client->setRedirectUri($redirect_uri);
    }

    public static function import() {

    }

}
