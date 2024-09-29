<?php

$start = microtime(true);

require_once('settings.php');
require_once('globalFunctions.php');

use Classes\ResourceManager;

ResourceManager::getParameters();
ResourceManager::pass();
ResourceManager::session();

if (!ResourceManager::handleCache()) {
	ResourceManager::initPage();
}

ResourceManager::close();
