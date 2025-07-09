<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use MaxBrennemann\PhpUtilities\DBAccess;

class CleanLogins implements Queueable
{
    public static function handle()
    {
        DBAccess::deleteQuery("DELETE FROM user_login_key WHERE expiration_date <= CURDATE();");
        DBAccess::deleteQuery("DELETE FROM user_devices WHERE last_usage <= DATE_SUB(NOW(),INTERVAL 1 YEAR);");
    }
}
