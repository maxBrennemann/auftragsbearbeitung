<?php

use Classes\Project\Table\TableConfig;
use MaxBrennemann\PhpUtilities\Migrations\UpgradeManager;

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once "settings.php";

ob_start();
TableConfig::generate();
$content = ob_get_clean();

file_put_contents("files/res/js/tableconfig.js", $content);

if (isset($argv) && count($argv) >= 2 && $argv[1] == "--force") {
    UpgradeManager::upgrade(true, "upgrade/Changes/");
} else {
    UpgradeManager::upgrade(false, "upgrade/Changes/");
}
