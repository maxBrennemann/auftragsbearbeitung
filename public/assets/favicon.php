<?php

if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false
    || strpos($_SERVER["HTTP_USER_AGENT"], "Trident") !== false) {
    header("Content-Type: image/vnd.microsoft.icon");
    readfile("../public/assets/img/favicon.ico");
} else {
    header("Content-Type: image/png");
    readfile("../public/assets/img/favicon.png");
}
