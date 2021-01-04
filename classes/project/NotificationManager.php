<?php

class NotificationManager {

    /*
    * gets all unviewed notifications counted and returned
    */
    public static function getNotificationCount() {
        if (isset($_SESSION['userid'])) {
            $user = $_SESSION['userid'];
            $result = DBAccess::selectQuery("SELECT COUNT(notification_id) FROM user_notifications WHERE user_id = $user AND ischecked = 'false'");
            if ($result == null) {
                return 0;
            } else {
                return $result[0]["COUNT(notification_id)"];
            }
        }
    }

    private static function getNotifications() {
        $user = $_SESSION['userid'];
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = $user AND ischecked = 'false'");
    }

    public static function htmlNotification() {
        $data = self::getNotifications();
        $htmlContent = "";

        foreach ($data as $n) {
            $htmlContent .= "<span>Art: <strong>" . $n['type'] . "</strong><br>" . $n['content'] . "</span>";
        }

        return $htmlContent;
    }

    public static function addNotification($user_id, $type, $content) {
        DBAccess::insertQuery("INSERT INTO user_notifications (user_id, `type`, content) VALUES ($user_id, '$type', '$content')");
    }

}

?>