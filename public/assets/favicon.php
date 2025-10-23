<?php

use Src\Classes\Link;

// TODO: allow upload of custom favicon, for now, it will point to the new default icons

if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false
    || strpos($_SERVER["HTTP_USER_AGENT"], "Trident") !== false) {

    if (!file_exists(ROOT . "public/assets/img/default_favicon.ico")) {
        $file = Link::getDefaultImage();
        header("Content-Type: image/png");
        readfile($file);
        return;
    }

    header("Content-Type: image/vnd.microsoft.icon");
    readfile(ROOT . "public/assets/img/default_favicon.ico");
} else {
    header("Content-Type: image/png");

    if (!file_exists(ROOT . "public/assets/img/default_favicon.png")) {
        $file = Link::getDefaultImage();
        readfile($file);
        return;
    }

    readfile(ROOT . "public/assets/img/default_favicon.png");
}
