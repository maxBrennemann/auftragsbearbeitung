<?php

$start = microtime(true);

require_once "settings.php";
require_once "globalFunctions.php";

use Classes\ResourceManager;
use Classes\MinifyFiles;

define("MINIFY_STATUS", MinifyFiles::isActivated());

ResourceManager::getParameters();
ResourceManager::pass();
ResourceManager::session();

if (!ResourceManager::handleCache()) {
	ResourceManager::initPage();
}

ResourceManager::close();
