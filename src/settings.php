<?php

date_default_timezone_set('Europe/Berlin');

require_once "../vendor/autoload.php";
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load("../.env");

use Src\Classes\Project\Config;
Config::load("../src/config.php");

require_once "../src/global-functions.php";
