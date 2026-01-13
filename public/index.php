<?php

use Src\Classes\ResourceManager;

$start = microtime(true);
ob_start();

require_once "../src/settings.php";

ResourceManager::manage();
