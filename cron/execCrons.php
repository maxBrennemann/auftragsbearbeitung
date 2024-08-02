<?php

$_SERVER["DOCUMENT_ROOT"] = "../";

require_once "settings.php";
require_once "vendor/autoload.php";
require_once "classes/DBAccess.php";
require_once "cron/CronManager.php";

CronManager::schedule();
