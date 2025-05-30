<?php

if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false 
    || strpos($_SERVER["HTTP_USER_AGENT"], "Trident") !== false) {
    header("Content-Type: image/vnd.microsoft.icon");
    readfile("./favicon.ico");
} else {
    header("Content-Type: image/png");
    readfile("./files/assets/img/favicon.png");
}
