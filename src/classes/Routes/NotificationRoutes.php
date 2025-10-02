<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class NotificationRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Notification\NotificationManager::htmlNotification()
     */
    protected static $getRoutes = [
        "/notification/template" => [\Src\Classes\Notification\NotificationManager::class, "htmlNotification"],
    ];

    protected static $postRoutes = [];

    /**
     * @uses \Src\Classes\Notification\NotificationManager::htmlNotification()
     */
    protected static $putRoutes = [
        "/notification/{id}/set-read" => [\Src\Classes\Notification\NotificationManager::class, ""],
    ];
}
