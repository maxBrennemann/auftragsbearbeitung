<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once('settings.php');

use Classes\MinifyFiles;
use Classes\Mailer;
use Upgrade\UpgradeManager;

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
