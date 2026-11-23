<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Notification\NotificationManager;

$limit = 200;
$notifications = NotificationManager::getNotificationsForUser("all", $limit);

echo TemplateController::getTemplate("notificationCenter", [
    "notifications" => $notifications,
    "unreadCount" => NotificationManager::getNotificationCount(),
    "limit" => $limit,
]);
