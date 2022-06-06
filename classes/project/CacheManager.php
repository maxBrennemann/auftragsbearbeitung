<?php

class CacheManager {

    function recache() {
        $cacheFile = "cache/cache_" . md5($_SERVER['REQUEST_URI']) . ".txt";
        if (file_exists($cacheFile)) {
           unlink($cacheFile); 
        }
    }

    static function cacheOff() {
        return DBAccess::updateQuery("UPDATE settings SET content = 'off' WHERE title = 'cacheStatus'");
    }

    static function cacheOn() {
        return DBAccess::updateQuery("UPDATE settings SET content = 'on' WHERE title = 'cacheStatus'");
    }

    static function getCacheStatus() {
        $query = "SELECT content FROM settings WHERE `title` = 'cacheStatus'";
        $status = DBAccess::selectQuery($query);
        $status = $status[0]['content'];
        return $status;
    }
}

?>