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
     */
    public static function getNotificationCount(): int
    {
        if (self::$notificationCount != -1) {
            return self::$notificationCount;
        }

        $user = User::getCurrentUserId();

        $query = "SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = :user AND ischecked = 0";
        $result = DBAccess::selectQuery($query, [
            "user" => $user
        ]);

        if ($result == null) {
            self::$notificationCount = 0;
            return 0;
        } else {
            self::$notificationCount = (int) $result[0]["c"];
            return (int) $result[0]["c"];
        }
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
        $query = "SELECT * FROM user_notifications WHERE user_id = :user AND ischecked = 0 AND `type` = 1";
        return DBAccess::selectQuery($query, [
            "user" => $user,
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
            case 1:
                $query = "SELECT Auftragsnummer FROM schritte WHERE Schrittnummer = :id";
                $orderData = DBAccess::selectQuery($query, [
                    "id" => $id,
                ]);
                $orderId = $orderData[0]["Auftragsnummer"];
                return Link::getPageLink("auftrag") . "?id=$orderId#stepCont";
            case 2:
                break;
            case 3:
                break;
            case 4:
                return Link::getPageLink("auftrag") . "?id=$id";
            case 5:
                break;
            case 6:
                break;
            case 7:
                break;
            default:
                break;
        }

        return "#";
    }

    private static function getTypeName(int $type): string
    {
        $types = [
            "erledigt",
            "Schritt",
            "",
            "",
            "Neuer Auftrag"
        ];
        return $types[$type] ?? "Benachrichtigung";
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
        if (!User::validate($user_id) && $user_id !== -1) {
            throw new Exception("User not found.");
        }

        if ($user_id == null || $user_id == -1) {
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
        $id = Tools::get("id");

        $query = "UPDATE user_notifications SET ischecked = 1 WHERE user_id = :uid AND id = :id";
        DBAccess::updateQuery($query, [
            "id" => $id,
            "uid" => $uid,
        ]);

        JSONResponseHandler::returnOK();
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
