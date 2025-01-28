<?php

use MaxBrennemann\PhpUtilities\Migrations\UpgradeManager;

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once "settings.php";

Classes\MinifyFiles::minify();

if (isset($argv) && count($argv) >= 2 && $argv[1] == "--force") {
    UpgradeManager::upgrade(true, "upgrade/Changes/");
} else {
    UpgradeManager::upgrade(false, "upgrade/Changes/");
}
