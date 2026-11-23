<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\SessionController;
use Src\Classes\Notification\NotificationManager;

set_time_limit(0);

/**
 * Server-Sent-Events-Stream, der die Anzahl ungelesener Benachrichtigungen
 * des angemeldeten Nutzers live an die Glocke pusht.
 */
class Events
{
    /** Wie lange eine Verbindung offen bleibt, bevor der Client neu verbindet. */
    private const LIFETIME = 30;

    /** Abstand zwischen zwei Prüfungen auf neue Benachrichtigungen (Sekunden). */
    private const INTERVAL = 2;

    public static function init(): void
    {
        if (!SessionController::isLoggedIn()) {
            http_response_code(401);
            return;
        }

        /*
         * Session-Datei freigeben: sonst blockieren parallele Requests desselben
         * Nutzers (z. B. "als gelesen markieren") bis der Stream endet.
         */
        session_write_close();

        self::outputHeader();

        $start = time();
        $lastCount = null;

        while (time() - $start < self::LIFETIME) {
            $count = NotificationManager::getNotificationCount(false);

            if ($count !== $lastCount) {
                $lastCount = $count;
                echo "event: notifications\n";
                echo "data: " . json_encode(["count" => $count]) . "\n\n";
                flush();
            }

            if (connection_aborted()) {
                return;
            }

            sleep(self::INTERVAL);
        }
    }

    private static function outputHeader(): void
    {
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
        /* Nginx soll den Stream nicht puffern, auch wenn fastcgi_buffering an ist. */
        header("X-Accel-Buffering: no");

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
}
