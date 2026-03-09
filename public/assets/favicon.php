<?php

use Src\Classes\Link;
use Src\Classes\Project\Image;

$favicon = Image::getFavicon();
if ($favicon !== "") {
    $uploadPath = Link::getFilePath($favicon, "upload");
    if (file_exists($uploadPath)) {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($uploadPath) ?: 'application/octet-stream';
        header("Content-Type: $mime");
        readfile($uploadPath);
        return;
    }
}

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
