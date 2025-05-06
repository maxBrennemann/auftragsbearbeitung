<?php

use Classes\Table\TableConfig;
use MaxBrennemann\PhpUtilities\Migrations\UpgradeManager;

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once "settings.php";

ob_start();
TableConfig::generate();
$content = ob_get_clean();

$target = "files/res/js/classes";
$destination = "node_modules/js-classes";

file_put_contents("$target/tableconfig.js", $content);
file_put_contents("$target/colorpicker.js", file_get_contents("node_modules/colorpicker/colorpicker.js"));
file_put_contents("$target/notifications.js", file_get_contents("$destination/notifications.js"));
file_put_contents("$target/ajax.js", file_get_contents("$destination/ajax.js"));
file_put_contents("$target/bindings.js", file_get_contents("$destination/bindings.js"));

if (isset($argv) && count($argv) >= 2 && $argv[1] == "--skip-migration") {
    return;
}

if (isset($argv) && count($argv) >= 2 && $argv[1] == "--force") {
    UpgradeManager::upgrade(true, "upgrade/Changes/");
} else {
    UpgradeManager::upgrade(false, "upgrade/Changes/");
}
