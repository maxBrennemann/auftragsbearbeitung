<?php

$start = microtime(true);

require_once "settings.php";
require_once "globalFunctions.php";

use Classes\ResourceManager;
use Classes\MinifyFiles;
use Classes\Project\CacheManager;

define("MINIFY_STATUS", MinifyFiles::isActivated());
define("CACHE_STATUS", CacheManager::getCacheStatus());

ResourceManager::getParameters();
ResourceManager::pass();
ResourceManager::session();

CacheManager::loadCacheIfExists();
ResourceManager::initPage();

ResourceManager::close();
