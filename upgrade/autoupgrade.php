<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once('settings.php');

Classes\MinifyFiles::minify();

if (isset($argv) && count($argv) >= 2 && $argv[1] == "--force") {
    Upgrade\UpgradeManager::upgrade(true);
} else {
    Upgrade\UpgradeManager::upgrade();
}
