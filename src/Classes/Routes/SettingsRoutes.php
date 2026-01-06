<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class SettingsRoutes extends Routes
{
    /**
     * @uses Classes\Project\Icon::ajaxIcon()
     * @uses Classes\Project\FileStats::getFilesInfo()
     * @uses Classes\Project\Settings::getUserSetting()
     * @uses Classes\Controller\CustomLinksController::getAvailableLinksAjax()
     */
    protected static $getRoutes = [
        "/settings/icon/{name}" => [\Src\Classes\Project\Icon::class, "ajaxIcon"],
        "/settings/files/info" => [\Src\Classes\Project\FileStats::class, "getFilesInfo"],
        "/settings/user/{userId}/{userSetting}" => [\Src\Classes\Project\Settings::class, "getUserSetting"],
        "/settings/links/available" => [\Src\Classes\Controller\CustomLinksController::class, "getAvailableLinksAjax"],
    ];

    /**
     * @uses Classes\Project\ClientSettings::createBackup()
     * @uses Classes\Project\ClientSettings::createFileBackup()
     * @uses Classes\Project\ClientSettings::addLogo()
     */
    protected static $postRoutes = [
        "/settings/backup" => [\Src\Classes\Project\ClientSettings::class, "createBackup"],
        "/settings/file-backup" => [\Src\Classes\Project\ClientSettings::class, "createFileBackup"],
        "/settings/add-logo" => [\Src\Classes\Project\ClientSettings::class, "addLogo"],
    ];

    /**
     * @uses Classes\Controller\TimeTrackingController::toggleDisplayTimeTracking()
     * @uses Classes\Project\ClientSettings::setFilterOrderPosten()
     * @uses Classes\Project\CacheManager::toggleCache()
     * @uses Classes\Project\Settings::updateConfig()
     */
    protected static $putRoutes = [
        "/settings/global-timetracking" => [\Src\Classes\Controller\TimeTrackingController::class, "toggleDisplayTimeTracking"],
        "/settings/filter-order-posten" => [\Src\Classes\Project\ClientSettings::class, "setFilterOrderPosten"],
        "/settings/cache" => [\Src\Classes\Project\CacheManager::class, "toggleCache"],
        "/settings/config/{configName}" => [\Src\Classes\Project\Settings::class, "updateConfig"],
    ];

    /**
     * @uses Classes\Project\CacheManager::deleteCache()
     */
    protected static $deleteRoutes = [
        "/settings/cache" => [\Src\Classes\Project\CacheManager::class, "deleteCache"],
    ];
}
