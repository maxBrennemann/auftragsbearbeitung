<?php

namespace Src\Classes\Notification;

use Exception;
use Src\Classes\Link;
use Src\Classes\Models\User as UserModel;
use Src\Classes\Project\User;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class NotificationManager
{
    private static int $notificationCount = -1;

    /**
     * gets all unviewed notifications counted and returned
     *
     * @param bool $useCache pass false for long running requests (e.g. the events stream) that must see fresh values
     */
    public static function getNotificationCount(bool $useCache = true): int
    {
        if ($useCache && self::$notificationCount != -1) {
            return self::$notificationCount;
        }

        $user = User::getCurrentUserId();

        $query = "SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = :user AND ischecked = 0";
        $result = DBAccess::selectQuery($query, [
            "user" => $user
        ]);

        $count = $result ? (int) $result[0]["c"] : 0;

        if ($useCache) {
            self::$notificationCount = $count;
        }

        return $count;
    }

    /**
     * @param int[] $includeTypes
     * @param int[] $excludeTypes
     * @return int
     */
    public static function getNotificationCountByType(array $includeTypes = [], array $excludeTypes = []): int
    {
        $userId = User::getCurrentUserId();
        $query = "SELECT COUNT(id) AS c
            FROM user_notifications
            WHERE user_id = ? AND ischecked = 0";
        $params = [$userId];

        if (!empty($includeTypes)) {
            $placeholders = implode(',', array_fill(0, count($includeTypes), '?'));
            $query .= " AND `type` IN ($placeholders)";
            $params = array_merge($params, $includeTypes);
        }

        if (!empty($excludeTypes)) {
            $placeholders = implode(',', array_fill(0, count($excludeTypes), '?'));
            $query .= " AND `type` NOT IN ($placeholders)";
            $params = array_merge($params, $excludeTypes);
        }

        $result = DBAccess::selectQuery($query, $params);
        return $result ? (int) $result[0]['c'] : 0;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function getTasks(): array
    {
        $user = User::getCurrentUserId();
        $query = "SELECT * FROM user_notifications WHERE user_id = :user AND ischecked = 0 AND `type` = :stepType";
        return DBAccess::selectQuery($query, [
            "user" => $user,
            "stepType" => NotificationType::TYPE_STEP,
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function getNews(): array
    {
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = :user AND ischecked = 0 AND `type` != :stepType", [
            "user" => User::getCurrentUserId(),
            "stepType" => NotificationType::TYPE_STEP,
        ]);
    }

    /**
     * @param int $limit
     * @return array<int, array<string, string>>
     */
    public static function getRecentNotifications(int $limit = 10): array
    {
        $user = User::getCurrentUserId();
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = :user ORDER BY created_at DESC LIMIT :limit", [
            "user" => $user,
            "limit" => $limit,
        ]);
    }

    /**
     * Returns the current user's notifications for the notification center.
     *
     * @param string $filter "all" or "unread"
     * @return array<int, array<string, mixed>>
     */
    public static function getNotificationsForUser(string $filter = "all", int $limit = 100): array
    {
        $query = "SELECT n.id, n.type, n.content, n.specific_id, n.ischecked, n.created_at,
                n.initiator, u.prename, u.lastname
            FROM user_notifications n
            LEFT JOIN `user` u ON u.id = n.initiator
            WHERE n.user_id = :user";

        if ($filter === "unread") {
            $query .= " AND n.ischecked = 0";
        }

        $query .= " ORDER BY n.created_at DESC, n.id DESC LIMIT :limit";

        $notifications = DBAccess::selectQuery($query, [
            "user" => User::getCurrentUserId(),
            "limit" => $limit,
        ]);

        foreach ($notifications as $key => $notification) {
            $notifications[$key]["typeName"] = NotificationType::getName((int) $notification["type"]);
            $notifications[$key]["category"] = NotificationType::getCategory((int) $notification["type"]);
            $notifications[$key]["link"] = self::getSpecificLink((int) $notification["type"], (int) $notification["specific_id"]);
            $notifications[$key]["initiatorName"] = trim(($notification["prename"] ?? "") . " " . ($notification["lastname"] ?? ""));
        }

        return $notifications;
    }

    public static function getNotificationCountAjax(): void
    {
        JSONResponseHandler::sendResponse([
            "count" => self::getNotificationCount(false),
        ]);
    }

    public static function checkActuality(): void
    {
        $user = User::getCurrentUserId();
        $query = "UPDATE user_notifications 
            JOIN schritte ON user_notifications.specific_id = schritte.Schrittnummer 
            SET ischecked = 1 
            WHERE user_notifications.`type` = 1 
                AND schritte.istErledigt = 0 
                AND user_id = $user;";
        DBAccess::updateQuery($query);
    }

    public static function htmlNotification(): void
    {
        $tasks = self::getTasks();
        $news = self::getNews();

        foreach ($tasks as $key => $task) {
            $tasks[$key]['typeName'] = self::getTypeName((int) $task['type']);
            $tasks[$key]['link'] = self::getSpecificLink((int) $task['type'], (int) $task['specific_id']);
        }

        foreach ($news as $key => $n) {
            $news[$key]['typeName'] = self::getTypeName((int) $n['type']);
            $news[$key]['link'] = self::getSpecificLink((int) $n['type'], (int) $n['specific_id']);
        }

        $tasksCount = self::getNotificationCountByType([NotificationType::TYPE_STEP]);
        $newsCount = self::getNotificationCountByType([], [NotificationType::TYPE_STEP]);

        $content = \Src\Classes\Controller\TemplateController::getTemplate("notificationMenu", [
            "tasks" => $tasks,
            "news" => $news,
            "tasksCount" => $tasksCount,
            "newsCount" => $newsCount,
        ]);
        JSONResponseHandler::sendResponse([
            "html" => $content,
        ]);
    }

    private static function getSpecificLink(int $type, int $id): string
    {
        switch ($type) {
            case NotificationType::TYPE_STEP:
                $orderData = DBAccess::selectQuery("SELECT Auftragsnummer FROM schritte WHERE Schrittnummer = :id", [
                    "id" => $id,
                ]);

                if (empty($orderData)) {
                    return "#";
                }

                $orderId = (int) $orderData[0]["Auftragsnummer"];
                return Link::getPageLink("auftrag") . "?id=$orderId#stepCont";
            case NotificationType::TYPE_NEW_ORDER:
            case NotificationType::ORDER_FINISHED:
                return Link::getPageLink("auftrag") . "?id=$id";
            case NotificationType::ORDER_PAYED:
                $invoiceData = DBAccess::selectQuery("SELECT order_id FROM invoice WHERE id = :id", [
                    "id" => $id,
                ]);

                if (empty($invoiceData)) {
                    return "#";
                }

                $orderId = (int) $invoiceData[0]["order_id"];
                return Link::getPageLink("auftrag") . "?id=$orderId";
            default:
                return "#";
        }
    }

    private static function getTypeName(int $type): string
    {
        return NotificationType::getName($type);
    }

    /**
     * addNotification adds a notification for a user or all users
     *
     * @param int|null $user_id     the id of the user who gets notified, if all users get notified, it is -1
     * @param int $type             the type of notification
     * @param string $content       the text content of the notification
     * @param int $specificId       the id connected with the type of notification e.g order id
     */
    public static function addNotification(?int $user_id, int $type, string $content, int $specificId): void
    {
        $initiator = User::getCurrentUserId();

        /* null or a non-positive id means: notify all users */
        if ($user_id === null || $user_id <= 0) {
            $user_id = -1;
        }

        /* check if it is a valid user, but leaving out our notify all */
        if ($user_id !== -1 && !User::validate($user_id)) {
            throw new Exception("User not found.");
        }

        if ($user_id === -1) {
            $query = "INSERT INTO user_notifications (`user_id`, `initiator`, `type`, content, specific_id) VALUES ";
            $allUsers = self::getAllUsers();
            $params = [];

            foreach ($allUsers as $user) {
                $params[] = [
                    $user["id"],
                    $initiator,
                    $type,
                    $content,
                    $specificId,
                ];
            }

            DBAccess::insertMultiple($query, $params);
        } else {
            DBAccess::insertQuery("INSERT INTO user_notifications (`user_id`, `initiator`, `type`, content, specific_id) VALUES (:userId, :initiator, :type, :content, :specificId)", [
                "userId" => $user_id,
                "initiator" => $initiator,
                "type" => $type,
                "content" => $content,
                "specificId" => $specificId,
            ]);
        }
    }

    /*
     * calls the addNotification function in order to set a notification if the specific trigger was bound to that notification
     */
    public static function addNotificationCheck(int $user_id, int $type, string $content, int $specificId): void
    {
        $query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = :specificId";
        DBAccess::updateQuery($query, ["specificId" => $specificId]);

        $query = "SELECT id FROM user_notifications WHERE specific_id = :specificId";
        $result = DBAccess::selectQuery($query, ["specificId" => $specificId]);

        if (!empty($result)) {
            self::addNotification($user_id, $type, $content, $specificId);
        }
    }

    public static function deleteById(int $type, int $specificId): void
    {
        $query = "DELETE FROM user_notifications WHERE `type` = :type AND specific_id = :specificId";
        DBAccess::deleteQuery($query, [
            "type" => $type,
            "specificId" => $specificId,
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function getAllUsers(): array
    {
        return UserModel::all();
    }

    public static function setNotificationsRead(): void
    {
        $uid = User::getCurrentUserId();
        $id = (int) Tools::get("id");

        $query = "UPDATE user_notifications SET ischecked = 1 WHERE user_id = :uid AND id = :id";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "uid" => $uid,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "count" => self::getNotificationCount(false),
        ]);
    }

    public static function setAllNotificationsRead(): void
    {
        $uid = User::getCurrentUserId();

        $query = "UPDATE user_notifications SET ischecked = 1 WHERE user_id = :uid";
        DBAccess::updateQuery($query, [
            "uid" => $uid,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "count" => 0,
        ]);
    }

    public static function notifyFromEntity(int $userId, NotifiableEntity $entity): void
    {
        self::addNotification(
            $userId,
            $entity->getNotificationType(),
            $entity->getNotificationContent(),
            $entity->getNotificationSpecificId()
        );
    }
}
