<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class NotificationRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Notification\NotificationManager::htmlNotification()
     */
    protected static $getRoutes = [
        "/notification/template" => [\Classes\Project\Notification\NotificationManager::class, "htmlNotification"],
    ];

    protected static $postRoutes = [];

    /**
     * @uses \Classes\Project\Notification\NotificationManager::htmlNotification()
     */
    protected static $putRoutes = [
        "/notification/{id}/set-read" => [\Classes\Project\Notification\NotificationManager::class, ""],
    ];
}
