<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once('settings.php');
require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');

$query = "SELECT `url` FROM crons;";
$result = DBAccess::selectQuery($query);

foreach ($result as $cron) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cron["url"]);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
}

?>