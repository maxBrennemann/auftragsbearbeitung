<?php

namespace Classes\Notification;

use Classes\Link;
use Classes\Models\User as UserModel;
use Classes\Project\User;
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

    public static function getNotificationCountByType(int ...$types): int
    {
        if (empty($types)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $query = "SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = ? AND ischecked = 0 AND `type` IN ($placeholders)";
        $params = array_merge([User::getCurrentUserId()], $types);
        $result = DBAccess::selectQuery($query, $params);

        return $result ? (int) $result[0]["c"] : 0;
    }

    private static function getTasks()
    {
        $user = User::getCurrentUserId();
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 0 AND `type` = 1");
    }

    private static function getNews()
    {
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = :user AND ischecked = 0 AND `type` != 1", [
            "user" => User::getCurrentUserId(),
        ]);
    }

    public static function getRecentNotifications(int $limit = 10): array
    {
        $user = User::getCurrentUserId();
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = :user ORDER BY created_at DESC LIMIT :limit", [
            "user" => $user,
            "limit" => $limit,
        ]);
    }

    public static function checkActuality()
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

    public static function htmlNotification()
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

        $tasksCount = self::getNotificationCountByType(NotificationType::TYPE_TASK);
        $newsCount = self::getNotificationCountByType(NotificationType::TYPE_STEP, NotificationType::TYPE_NEW_ORDER);

        $content = \Classes\Controller\TemplateController::getTemplate("notificationMenu", [
            "tasks" => $tasks,
            "news" => $news,
            "tasksCount" => $tasksCount,
            "newsCount" => $newsCount,
        ]);
        JSONResponseHandler::sendResponse([
            "html" => $content,
        ]);
    }

    private static function getSpecificLink($type, $id)
    {
        switch ($type) {
            case 1:
                $orderId = DBAccess::selectQuery("SELECT Auftragsnummer FROM schritte WHERE Schrittnummer = $id")[0]['Auftragsnummer'];
                return Link::getPageLink("auftrag") . "?id=$orderId";
            case 0:
                break;
            case 4:
                return Link::getPageLink("auftrag") . "?id=$id";
            default:
                break;
        }

        return "#";
    }

    private static function getTypeName($type)
    {
        $types = ["erledigt", "Schritt", "", "", "Neuer Auftrag"];
        return $types[$type];
    }

    /**
     * addNotification adds a notification for a user or all users
     *
     * @param int|null $user_id     the id of the user who gets notified, if all users get notified, it is -1
     * @param int $type             the type of notification
     * @param string  $content      the text content of the notification
     * @param int $specificId       the id connected with the type of notification e.g order id
     */
    public static function addNotification($user_id, $type, $content, $specificId)
    {
        $initiator = User::getCurrentUserId();

        if ($user_id == null) {
            $query = "INSERT INTO user_notifications (`user_id`, `initiator`, `type`, content, specific_id) VALUES ";
            $allUsers = self::getAllUsers();
            foreach ($allUsers as $u) {
                $id = $u["id"];
                $query .= "($id, $initiator, $type, '$content', $specificId),";
            }
            $query = substr($query, 0, -1);
            DBAccess::insertQuery($query);
        } else {
            DBAccess::insertQuery("INSERT INTO user_notifications (`user_id`, `initiator`, `type`, content, specific_id) VALUES ($user_id, $initiator, $type, '$content', $specificId)");
        }
    }

    /*
     * calls the addNotification function in order to set a notification if the specific trigger was bound to that notification
     */
    public static function addNotificationCheck($user_id, $type, $content, $specificId)
    {
        $query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = $specificId";
        DBAccess::updateQuery($query);

        $query = "SELECT id FROM user_notifications WHERE specific_id = $specificId";
        $result = DBAccess::selectQuery($query);

        if (!empty($result)) {
            self::addNotification($user_id, $type, $content, $specificId);
        }
    }

    private static function getAllUsers(): array
    {
        $users = new UserModel();
        return $users->all();
    }

    public static function setNotificationsRead()
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
