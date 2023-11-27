<?php

$start = microtime(true);

require_once('settings.php');
require_once('globalFunctions.php');
require_once('classes/ResourceManager.php');

ResourceManager::pass();
ResourceManager::session();

/* TODO: alle db accesses auf parameterizes sql umstellen wegen sql injections */

if (!ResourceManager::handleCache()) {
	ResourceManager::initPage();
}

ResourceManager::close();
