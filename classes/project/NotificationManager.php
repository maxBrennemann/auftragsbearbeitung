<?php

require_once('classes/Link.php');

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
class NotificationManager {

    /*
    * gets all unviewed notifications counted and returned
    */
    public static function getNotificationCount() {
        if (isset($_SESSION['userid'])) {
            $user = $_SESSION['userid'];
            $result = DBAccess::selectQuery("SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = $user AND ischecked = 'false'");
            if ($result == null) {
                return 0;
            } else {
                return $result[0]["c"];
            }
        }
    }

    public static function getTaskCount() {
        if (isset($_SESSION['userid'])) {
            $user = $_SESSION['userid'];
            $result = DBAccess::selectQuery("SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = $user AND ischecked = 'false' AND (`type` = 1 OR `type`= 0)");
            if ($result == null) {
                return 0;
            } else {
                return $result[0]["c"];
            }
        }
    }

    public static function getNewsCount() {
        if (isset($_SESSION['userid'])) {
            $user = $_SESSION['userid'];
            $result = DBAccess::selectQuery("SELECT COUNT(id) AS c FROM user_notifications WHERE user_id = $user AND ischecked = 'false' AND `type` = 4");
            if ($result == null) {
                return 0;
            } else {
                return $result[0]["c"];
            }
        }
    }

    private static function getNotifications() {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false'");
    }

    private static function getTasks() {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false' AND (`type` = 1 OR `type`= 0)");
    }

    private static function getNews() {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false' AND `type` = 4");
    }

    public static function htmlNotification() {
        $tasks = self::getTasks();
        $news = self::getNews();

        $tasksCount = self::getTaskCount();
        $newsCount = self::getNewsCount();
        ?>
        <h3>Benachrichtigungen und Aufgaben</h3>
        <div style="display: block;">
            <!-- content -->
            <h4>Meine Aufgaben (<?=$tasksCount?>)</h4>
            <?php foreach ($tasks as $n): ?>
            <span><strong><?=self::getTypeName((int) $n["type"])?>: </strong><a href="<?=self::getSpecificLink((int) $n['type'], (int) $n['specific_id'])?>"><?=$n["content"]?></a><br></span>
            <?php endforeach; ?>
        </div>
        <div style="display: block;">
            <!-- content -->
            <h4>Benachrichtigungen und Neuigkeiten (<?=$newsCount?>)</h4>
            <h6><a href="#" onclick="setRead()">Alles als gelesen markieren</a></h6>
            <?php foreach ($news as $n): ?>
            <span><strong><?=self::getTypeName((int) $n["type"])?>: </strong><a href="<?=self::getSpecificLink((int) $n['type'], (int) $n['specific_id'])?>"><?=$n["content"]?></a><br></span>
            <?php endforeach; ?>
        </div>
        <p><a href="#">Ältere Benachrichtigungen anzeigen</a></p>
        <?php
    }

    private static function getSpecificLink($type, $id) {
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

    private static function getTypeName($type) {
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
    public static function addNotification($user_id, $type, $content, $specificId) {
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
    public static function addNotificationCheck($user_id, $type, $content, $specificId) {
        $query = "UPDATE user_notifications SET ischecked = 1 WHERE specific_id = $specificId";
		DBAccess::updateQuery($query);

        $query = "SELECT id FROM user_notifications WHERE specific_id = $specificId";
        $result = DBAccess::selectQuery($query);

        if (!empty($result))
            self::addNotification($user_id, $type, $content, $specificId);
    }

    private static function getAllUsers() {
        $query = "SELECT id_mitarbeiter as id FROM members_mitarbeiter";
        $data = DBAccess::selectQuery($query);
        return $data;
    }

    public static function setNotificationsRead($notifications) {
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

?>