<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class NotificationRoutes extends Routes
{
    /**
     * @uses \Classes\Notification\NotificationManager::htmlNotification()
     */
    protected static $getRoutes = [
        "/notification/template" => [\Classes\Notification\NotificationManager::class, "htmlNotification"],
    ];

    protected static $postRoutes = [];

    /**
     * @uses \Classes\Notification\NotificationManager::htmlNotification()
     */
    protected static $putRoutes = [
        "/notification/{id}/set-read" => [\Classes\Notification\NotificationManager::class, ""],
    ];
}
