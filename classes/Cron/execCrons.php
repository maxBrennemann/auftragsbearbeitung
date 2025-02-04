<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once "settings.php";
require_once "vendor/autoload.php";

\Classes\Cron\CronManager::run();
