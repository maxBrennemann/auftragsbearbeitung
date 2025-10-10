<?php

namespace Src\Classes\Notification;

interface NotifiableEntity
{
    public function getNotificationContent(): string;
    public function getNotificationType(): int;
    public function getNotificationLink(): string;
    public function getNotificationSpecificId(): int;
}
