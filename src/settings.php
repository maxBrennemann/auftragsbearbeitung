<?php

date_default_timezone_set('Europe/Berlin');
define('ROOT', realpath(__DIR__ . '/../') . '/');

require_once ROOT . "vendor/autoload.php";
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(ROOT . ".env");

use Src\Classes\Project\Config;
Config::load(ROOT . "src/config.php");
Config::loadLanguages();

require_once ROOT . "src/global-functions.php";
