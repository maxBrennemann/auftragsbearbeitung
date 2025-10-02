<?php

$start = microtime(true);

require_once "src/settings.php";

use Src\Classes\Controller\SessionController;
use Src\Classes\Project\CacheManager;
use Src\Classes\ResourceManager;

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
