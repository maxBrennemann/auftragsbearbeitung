<?php

use Src\Classes\Link;

if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false
    || strpos($_SERVER["HTTP_USER_AGENT"], "Trident") !== false) {

    if (!file_exists("../public/assets/img/favicon.ico")) {
        $file = Link::getDefaultImage();
        header("Content-Type: image/png");
        readfile($file);
        return;
    }

    header("Content-Type: image/vnd.microsoft.icon");
    readfile("../public/assets/img/favicon.ico");
} else {
    header("Content-Type: image/png");

    if (!file_exists("../public/assets/img/favicon.png")) {
        $file = Link::getDefaultImage();
        readfile($file);
        return;
    }

    readfile("../public/assets/img/favicon.png");
}
