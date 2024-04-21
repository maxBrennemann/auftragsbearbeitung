<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once('settings.php');
require_once('classes/DBAccess.php');
require_once('classes/Mailer.php');

require_once("classes/MinifyFiles.php");
require_once("upgrade/UpgradeManager.php");

MinifyFiles::minify();
$files = UpgradeManager::checkNewSQL();
$errors = [];

foreach ($files as $file) {
    try {
        UpgradeManager::executeNewSQLQueries($file);
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

if (count($errors) > 0) {
    echo "Fehler beim Ausführen der SQL-Dateien:";
    foreach ($errors as $error) {
        echo $error;
    }

    Mailer::sendMail($_ENV["MAIL_ADDRESS_ALERTS"], "Fehler beim Autoupgrade", "Fehler beim Ausführen der SQL-Dateien: " . implode("\n", $errors), "error@staging.organiserung.b-schriftung.de");
}
