<?php

require_once('classes/Link.php');

/*
 * notification types:
 *  0 -> undefined / unset
 *  1 -> Bearbeitungsschritt
 *  2 -> Posten
 *  3 -> Datei
 */
class NotificationManager {

    /*
    * gets all unviewed notifications counted and returned
    */
    public static function getNotificationCount() {
        if (isset($_SESSION['userid'])) {
            $user = $_SESSION['userid'];
            $result = DBAccess::selectQuery("SELECT COUNT(id) FROM user_notifications WHERE user_id = $user AND ischecked = 'false'");
            if ($result == null) {
                return 0;
            } else {
                return $result[0]["COUNT(id)"];
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
            $type = self::getTypeName((int) $n['type']);
            $link = self::getSpecificLink((int) $n['specific_id']);
            $htmlContent .= "<span><strong>" . $type . ": </strong><a href=\"$link\">" . $n['content'] . "</a></span>";
        }

        return $htmlContent;
    }

    private static function getSpecificLink($type) {
        switch ($type) {
            case 1:
                $orderId = DBAccess::selectQuery("SELECT Auftragsnummer FROM schritte WHERE SChrittnummer = $type")[0]['Auftragsnummer'];
                return Link::getPageLink("auftrag") . "?id=$orderId";
                break;
            case 0:
            default:
                break;
        }
    }

    private static function getTypeName($type) {
        $types = ["", "Schritt"];
        return $types[$type];
    }

    public static function addNotification($user_id, $type, $content, $specificId) {
        DBAccess::insertQuery("INSERT INTO user_notifications (user_id, `type`, content, specific_id) VALUES ($user_id, $type, '$content', $specificId)");
    }

}

?>