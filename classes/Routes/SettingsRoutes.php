<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class SettingsRoutes extends Routes
{

    protected static $getRoutes = [];

    /**
     * @uses Classes\Project\ClientSettings::createBackup()
     * @uses Classes\MinifyFiles::minifyRequest()
     */
    protected static $postRoutes = [
        "/settings/backup" => [\Classes\Project\ClientSettings::class, "createBackup"],
        "/settings/minify" => [\Classes\MinifyFiles::class, "minifyRequest"],
    ];

    /**
     * @uses Classes\Project\TimeTrackingController::toggleDisplayTimeTracking()
     * @uses Classes\Project\ClientSettings::setFilterOrderPosten()
     * @uses Classes\Project\ClientSettings::setGrayScale()
     * @uses Classes\Project\CacheManager::toggleCache()
     * @uses Classes\Project\CacheManager::toggleMinify()
     * @uses Classes\Project\ClientSettings::createBackup()
     * @uses Classes\Project\Config::setDefaultWage()
     */
    protected static $putRoutes = [
        "/settings/global-timetracking" => [\Classes\Project\TimeTrackingController::class, "toggleDisplayTimeTracking"],
        "/settings/filter-order-posten" => [\Classes\Project\ClientSettings::class, "setFilterOrderPosten"],
        "/settings/color" => [\Classes\Project\ClientSettings::class, "setGrayScale"],
        "/settings/cache" => [\Classes\Project\CacheManager::class, "toggleCache"],
        "/settings/minify" => [\Classes\Project\CacheManager::class, "toggleMinify"],
        "/settings/default-wage" => [\Classes\Project\Config::class, "setDefaultWage"],
    ];

    /**
     * @uses Classes\Project\CacheManager::deleteCache()
     */
    protected static $deleteRoutes = [
        "/settings/cache" => [\Classes\Project\CacheManager::class, "deleteCache"],
    ];
}
