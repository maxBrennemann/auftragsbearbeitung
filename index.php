<?php

$start = microtime(true);

require_once('settings.php');
require_once('globalFunctions.php');

use Classes\ResourceManager;

ResourceManager::getParameters();
ResourceManager::pass();
ResourceManager::session();

/* TODO: alle db accesses auf parameterizes sql umstellen wegen sql injections */

if (!ResourceManager::handleCache()) {
	ResourceManager::initPage();
}

ResourceManager::close();
