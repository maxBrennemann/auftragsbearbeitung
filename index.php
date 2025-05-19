<?php

$start = microtime(true);

require_once "settings.php";

use Classes\Auth\SessionController;

use Classes\ResourceManager;
use Classes\Project\CacheManager;

ResourceManager::getParameters();
ResourceManager::initialize();

SessionController::start();

ResourceManager::pass();

CacheManager::loadCacheIfExists();
ResourceManager::initPage();

ResourceManager::close();
