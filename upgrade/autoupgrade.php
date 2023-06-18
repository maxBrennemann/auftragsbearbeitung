<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once('settings.php');
require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');

require_once("classes/MinifyFiles.php");
require_once("upgrade/UpgradeManager.php");

MinifyFiles::minify();
$files = UpgradeManager::checkNewSQL();

foreach ($files as $file) {
    UpgradeManager::executeNewSQLQueries($file);
}

?>