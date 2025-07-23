<?php

$start = microtime(true);

require_once "helpers/settings.php";

use Classes\Controller\SessionController;
use Classes\Project\CacheManager;
use Classes\ResourceManager;

ResourceManager::getParameters();
ResourceManager::initialize();
ResourceManager::identifyRequestType();

SessionController::start();

ResourceManager::pass();

CacheManager::loadCacheIfExists();

register_shutdown_function("captureError");

try {
    ResourceManager::initPage();
} catch (Throwable $e) {
    $_ENV["LAST_EXCEPTION"] = $e;
}

ResourceManager::close();
