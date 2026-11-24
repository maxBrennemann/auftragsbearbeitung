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
        $query = "SELECT * FROM user_notifications WHERE user_id = :user AND ischecked = 0 AND `type` = :stepType ORDER BY created_at DESC, id DESC";
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
        return DBAccess::selectQuery("SELECT * FROM user_notifications WHERE user_id = :user AND ischecked = 0 AND `type` != :stepType ORDER BY created_at DESC, id DESC", [
            "user" => User::getCurrentUserId(),
            "stepType" => NotificationType::TYPE_STEP,
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

        $notifications = self::attachDisplayData($notifications);

        foreach ($notifications as $key => $notification) {
            $notifications[$key]["category"] = NotificationType::getCategory((int) $notification["type"]);
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

    public static function htmlNotification(): void
    {
        $tasks = self::attachDisplayData(self::getTasks());
        $news = self::attachDisplayData(self::getNews());

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

    /**
     * Ergänzt typeName und link. Die Auftragsnummern für Schritte und Rechnungen
     * werden gesammelt in je einer Abfrage geladen statt einzeln pro Zeile.
     *
     * @param array<int, array<string, mixed>> $notifications
     * @return array<int, array<string, mixed>>
     */
    private static function attachDisplayData(array $notifications): array
    {
        $stepIds = [];
        $invoiceIds = [];

        foreach ($notifications as $notification) {
            $type = (int) $notification["type"];
            $id = (int) $notification["specific_id"];

            if ($type === NotificationType::TYPE_STEP || $type === NotificationType::TYPE_STEP_UPDATE) {
                $stepIds[$id] = $id;
            } elseif ($type === NotificationType::ORDER_PAYED) {
                $invoiceIds[$id] = $id;
            }
        }

        $orderByStep = self::loadOrderIds("SELECT Schrittnummer AS id, Auftragsnummer AS orderId FROM schritte WHERE Schrittnummer IN", $stepIds);
        $orderByInvoice = self::loadOrderIds("SELECT id, order_id AS orderId FROM invoice WHERE id IN", $invoiceIds);
        $orderLink = Link::getPageLink("auftrag");

        foreach ($notifications as $key => $notification) {
            $type = (int) $notification["type"];
            $id = (int) $notification["specific_id"];

            $link = match ($type) {
                NotificationType::TYPE_STEP, NotificationType::TYPE_STEP_UPDATE => isset($orderByStep[$id])
                    ? "$orderLink?id={$orderByStep[$id]}#stepCont" : "",
                NotificationType::TYPE_NEW_ORDER, NotificationType::ORDER_FINISHED => $id > 0
                    ? "$orderLink?id=$id" : "",
                NotificationType::ORDER_PAYED => isset($orderByInvoice[$id])
                    ? "$orderLink?id={$orderByInvoice[$id]}" : "",
                default => "",
            };

            $notifications[$key]["typeName"] = NotificationType::getName($type);
            /* leerer Link: es gibt kein Ziel mehr (z. B. gelöschter Schritt), die View zeigt dann nur Text */
            $notifications[$key]["link"] = $link;
        }

        return $notifications;
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, int> id => Auftragsnummer
     */
    private static function loadOrderIds(string $queryPrefix, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(",", array_fill(0, count($ids), "?"));
        $rows = DBAccess::selectQuery("$queryPrefix ($placeholders)", array_values($ids));

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row["id"]] = (int) $row["orderId"];
        }

        return $map;
    }

    /**
     * addNotification adds a notification for a user or all users
     *
     * Bei einer Benachrichtigung an alle wird die auslösende Person ausgenommen – sie weiß
     * ja schon, was sie getan hat. Aufgaben (TYPE_STEP) gehen dagegen an alle, auch an sie selbst.
     *
     * @param int|null $user_id     the id of the user who gets notified, if all users get notified, it is -1
     * @param int $type             the type of notification
     * @param string $content       the text content of the notification
     * @param int $specificId       the id connected with the type of notification e.g order id
     */
    public static function addNotification(?int $user_id, int $type, string $content, int $specificId): void
    {
        $initiator = User::getCurrentUserId();
        $content = mb_substr($content, 0, 128);

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
            $params = [];

            foreach (self::getAllUsers() as $user) {
                if ($type !== NotificationType::TYPE_STEP && (int) $user["id"] === $initiator) {
                    continue;
                }

                $params[] = [
                    $user["id"],
                    $initiator,
                    $type,
                    $content,
                    $specificId,
                ];
            }

            if (!empty($params)) {
                DBAccess::insertMultiple($query, $params);
            }
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

    public static function deleteById(int $type, int $specificId): void
    {
        $query = "DELETE FROM user_notifications WHERE `type` = :type AND specific_id = :specificId";
        DBAccess::deleteQuery($query, [
            "type" => $type,
            "specificId" => $specificId,
        ]);
    }

    /**
     * Ein erledigter Schritt ist für niemanden mehr eine offene Aufgabe:
     * die Aufgaben-Benachrichtigungen aller Nutzer dazu werden als gelesen markiert.
     */
    public static function resolveStepNotifications(int $stepId): void
    {
        DBAccess::updateQuery("UPDATE user_notifications SET ischecked = 1 WHERE `type` = :type AND specific_id = :stepId", [
            "type" => NotificationType::TYPE_STEP,
            "stepId" => $stepId,
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
}
