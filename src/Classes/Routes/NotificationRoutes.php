<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class NotificationRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Notification\NotificationManager::htmlNotification()
     * @uses \Src\Classes\Notification\NotificationManager::getNotificationCountAjax()
     */
    protected static $getRoutes = [
        "/notification/template" => [\Src\Classes\Notification\NotificationManager::class, "htmlNotification"],
        "/notification/count" => [\Src\Classes\Notification\NotificationManager::class, "getNotificationCountAjax"],
    ];

    protected static $postRoutes = [];

    /**
     * @uses \Src\Classes\Notification\NotificationManager::setNotificationsRead()
     * @uses \Src\Classes\Notification\NotificationManager::setAllNotificationsRead()
     */
    protected static $putRoutes = [
        "/notification/{id}/set-read" => [\Src\Classes\Notification\NotificationManager::class, "setNotificationsRead"],
        "/notification/read-all" => [\Src\Classes\Notification\NotificationManager::class, "setAllNotificationsRead"],
    ];
}
