<?php

namespace Classes\Project;

use Classes\DBAccess;
use Classes\Link;

/*
 * notification types:
 *  0 -> undefined / unset
 *  1 -> Bearbeitungsschritt
 *  2 -> Posten
 *  3 -> Datei
 *  4 -> neuer Auftrag
 * 
 *  Neuigkeiten erstellen bei:
 *      - neuer Auftrag
 *      - neuer Kunde
 *      - Auftrag abgeschlossen
 *      - Rechnung erstellt
 *      -> kann erweitert werden
 */

class NotificationManager
{

    /**
     * returns the user id of the current user
     * @return int|null
     */
    private static function getUserId(): ?int
    {
        if (isset($_SESSION['userid'])) {
            return $_SESSION['userid'];
        } else {
            return null;
        }
    }

    /**
     * gets all unviewed notifications counted and returned
     */
    public static function getNotificationCount()
    {
        $user = self::getUserId();
        if ($user == null) {
            return 0;
        }

        $result = DBAccess::selectQuery("SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = :user AND ischecked = 'false'", [":user" => $user]);

        if ($result == null) {
            return 0;
        } else {
            return $result[0]["c"];
        }
    }

    public static function getTaskCount()
    {
        $user = self::getUserId();
        if ($user == null) {
            return 0;
        }

        $result = DBAccess::selectQuery("SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = :user AND ischecked = 'false' AND (`type` = 1)", [":user" => $user]);

        if ($result == null) {
            return 0;
        } else {
            return $result[0]["c"];
        }
    }

    public static function getNewsCount()
    {
        $user = self::getUserId();
        if ($user == null) {
            return 0;
        }

        $result = DBAccess::selectQuery("SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = :user AND ischecked = 'false' AND (`type` = 4 OR `type` = 0)", [":user" => $user]);

        if ($result == null) {
            return 0;
        } else {
            return $result[0]["c"];
        }
    }

    private static function getNotifications()
    {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false'");
    }

    private static function getTasks()
    {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false' AND (`type` = 1)");
    }

    private static function getNews()
    {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false' AND (`type` = 4 OR `type`= 0)");
    }

    public static function checkActuality()
    {
        $user = $_SESSION['userid'];
        $query = "UPDATE user_notifications JOIN schritte ON user_notifications.specific_id = schritte.Schrittnummer SET ischecked = 1 WHERE user_notifications.`type` = 1 AND schritte.istErledigt = 0 AND user_id = $user;";
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

        $tasksCount = self::getTaskCount();
        $newsCount = self::getNewsCount();

        ob_start();
        insertTemplate('files/res/views/notificationMenuView.php', [
            "tasks" => $tasks,
            "news" => $news,
            "tasksCount" => $tasksCount,
            "newsCount" => $newsCount
        ]);
        $content = ob_get_clean();
        return $content;
    }

    private static function getSpecificLink($type, $id)
    {
        switch ($type) {
            case 1:
                $orderId = DBAccess::selectQuery("SELECT Auftragsnummer FROM schritte WHERE Schrittnummer = $id")[0]['Auftragsnummer'];
                return Link::getPageLink("auftrag") . "?id=$orderId";
                break;
            case 0:
                break;
            case 4:
                return Link::getPageLink("auftrag") . "?id=$id";
                break;
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
     * @param integer $user_id      the id of the user who gets notified, if all users get notified, it is -1
     * @param integer $type         the type of notification
     * @param string  $content      the text content of the notification
     * @param integer $specificId   the id connected with the type of notification e.g order id
     */
    public static function addNotification($user_id, $type, $content, $specificId)
    {
        $initiator = 0;
        if (isset($_SESSION['userid']))
            $initiator = $_SESSION['userid'];

        if ($user_id == -1) {
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

        if (!empty($result))
            self::addNotification($user_id, $type, $content, $specificId);
    }

    private static function getAllUsers()
    {
        $query = "SELECT id FROM members";
        $data = DBAccess::selectQuery($query);
        return $data;
    }

    public static function setNotificationsRead($notifications)
    {
        if (isset($_SESSION['userid']))
            $uid = $_SESSION['userid'];
        else
            return null;

        if ($notifications == -1) {
            $query = "UPDATE user_notification SET ischecked = 1 WHERE user_id = $uid AND `type` = 4";
            DBAccess::updateQuery($query);
        } else {
            foreach ($notifications as $notification) {
                $query = "UPDATE user_notification SET ischecked = 1 WHERE id = $notification";
                DBAccess::updateQuery($query);
            }
        }
    }
}
