<?php

define('CURRENTVERSION', '1.1.18');
ini_set('display_errors', true);

use MaxBrennemann\PhpUtilities\JSONResponseHandler;

function printError($message) {
	if ($_ENV["DEV_MODE"] == "true") {
		JSONResponseHandler::throwError(500, $message);
	} else {
		JSONResponseHandler::throwError(500, "Internal server error");
	}

	die();
}

function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }

	printError([
		"message" => $message,
		"file" => $file,
		"line" => $line,
		"severity" => $severity
	]);
}

set_error_handler("exception_error_handler");

function fatal_handler() {
	$error = error_get_last();

	if ($error == null) {
		return;
	}

	$message = $error["message"];
	$file = $error["file"];
	$line = $error["line"];
	$severity = $error["type"];

	printError([
		"message" => $message,
		"file" => $file,
		"line" => $line,
		"severity" => $severity
	]);
}

register_shutdown_function("fatal_handler");

/**
 * https://stackoverflow.com/questions/2236668/file-get-contents-breaks-up-utf-8-characters
 */
function file_get_contents_utf8($fn) {
	$content = file_get_contents($fn);
	return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

/**
 * polyfill str_contains, maybe remove it later when support for older version drops
 * https://www.php.net/manual/en/function.str-contains.php
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function isLoggedIn() {
	if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == true) {
		return true;	
	}
	return false;
}

function getCurrentVersion() {
	return CURRENTVERSION;
}

function errorReporting() {
	if ($_ENV["ERRORREPORTING"]) {
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}

	ini_set('display_errors', false);
}

function getParameter($value, $type = "GET", $default = "") {
	switch ($type) {
		case "GET":
			if (isset($_GET[$value])) {
				return $_GET[$value];
			}
			break;
		case "POST":
			if (isset($_POST[$value])) {
				return $_POST[$value];
			}
			break;
	}
	return $default;
}

function insertTemplate($path, array $parameters = []) {
	if (file_exists($path)) {
		extract($parameters);
		include $path;
	}
}

/** https://stackoverflow.com/a/2792045/7113688 */
function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
    $str = str_replace('-', '', ucwords($string, '-'));

    if (!$capitalizeFirstCharacter) {
        $str = lcfirst($str);
    }

    return $str;
}
