<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SettingsRoutes extends Routes
{

    /**
     * @uses Classes\Project\Icon::ajaxIcon()
     * @uses Classes\Project\Config::getFilesInfo()
     */
    protected static $getRoutes = [
        "/settings/icon/{name}" => [\Classes\Project\Icon::class, "ajaxIcon"],
        "/settings/files/info" => [\Classes\Project\Config::class, "getFilesInfo"],
    ];

    /**
     * @uses Classes\Project\ClientSettings::createBackup()
     * @uses Classes\Project\ClientSettings::createFileBackup()
     * @uses Classes\Project\ClientSettings::addLogo()
     */
    protected static $postRoutes = [
        "/settings/backup" => [\Classes\Project\ClientSettings::class, "createBackup"],
        "/settings/file-backup" => [\Classes\Project\ClientSettings::class, "createFileBackup"],
        "/settings/add-logo" => [\Classes\Project\ClientSettings::class, "addLogo"],
    ];

    /**
     * @uses Classes\Controller\TimeTrackingController::toggleDisplayTimeTracking()
     * @uses Classes\Project\ClientSettings::setFilterOrderPosten()
     * @uses Classes\Project\CacheManager::toggleCache()
     * @uses Classes\Project\CacheManager::toggleMinify()
     * @uses Classes\Project\Config::updateConfig()
     */
    protected static $putRoutes = [
        "/settings/global-timetracking" => [\Classes\Controller\TimeTrackingController::class, "toggleDisplayTimeTracking"],
        "/settings/filter-order-posten" => [\Classes\Project\ClientSettings::class, "setFilterOrderPosten"],
        "/settings/cache" => [\Classes\Project\CacheManager::class, "toggleCache"],
        "/settings/minify" => [\Classes\Project\CacheManager::class, "toggleMinify"],
        "/settings/config/{configName}" => [\Classes\Project\Config::class, "updateConfig"],
    ];

    /**
     * @uses Classes\Project\CacheManager::deleteCache()
     */
    protected static $deleteRoutes = [
        "/settings/cache" => [\Classes\Project\CacheManager::class, "deleteCache"],
    ];
}
