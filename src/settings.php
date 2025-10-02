<?php

date_default_timezone_set('Europe/Berlin');

require_once "./vendor/autoload.php";
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(".config/.env");

require_once "src/global-functions.php";
