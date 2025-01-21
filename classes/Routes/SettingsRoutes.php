<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SettingsRoutes extends Routes
{

    protected static $getRoutes = [];

    /**
     * @uses Classes\Project\ClientSettings::createBackup()
     */
    protected static $postRoutes = [
        "/settings/backup" => [\Classes\Project\ClientSettings::class, "createBackup"],
    ];

    /**
     * @uses Classes\Project\TimeTrackingController::toggleDisplayTimeTracking()
     * @uses Classes\Project\ClientSettings::setFilterOrderPosten()
     * @uses Classes\Project\ClientSettings::setGrayScale()
     * @uses Classes\Project\CacheManager::toggleCache()
     * @uses Classes\Project\CacheManager::toggleMinify()
     * @uses Classes\Project\ClientSettings::createBackup()
     */
    protected static $putRoutes = [
        "/settings/global-timetracking" => [\Classes\Project\TimeTrackingController::class, "toggleDisplayTimeTracking"],
        "/settings/filter-order-posten" => [\Classes\Project\ClientSettings::class, "setFilterOrderPosten"],
        "/settings/color" => [\Classes\Project\ClientSettings::class, "setGrayScale"],
        "/settings/cache" => [\Classes\Project\CacheManager::class, "toggleCache"],
        "/settings/minify" => [\Classes\Project\CacheManager::class, "toggleMinify"],
    ];
}
