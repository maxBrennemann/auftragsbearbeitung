<?php

class CacheManager {

    function recache() {
        $cacheFile = "cache/cache_" . md5($_SERVER['REQUEST_URI']) . ".txt";
        if (file_exists($cacheFile)) {
           unlink($cacheFile); 
        }
    }
}

?>