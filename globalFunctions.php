<?php

define('CURRENTVERSION', '1.2.1');
ini_set('display_errors', true);

use Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

function printError($message)
{
	if ($_ENV["DEV_MODE"] == "true") {
		JSONResponseHandler::throwError(500, $message);
	} else {
		JSONResponseHandler::throwError(500, "Internal server error");
	}

	die();
}

function exception_error_handler($severity, $message, $file, $line)
{
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

function fatal_handler()
{
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

function captureError()
{
	$error = error_get_last();
	$e = $_ENV["LAST_EXCEPTION"] ?? null;

	if (!$error && $e == null) {
		return;
	}

	$errorTempalte = "";

	if ($_ENV["DEV_MODE"]) {
		$errorData = [];

		if ($e instanceof Throwable) {
			$errorData["type"] = "Uncaught Exception: " . get_class($e);
			$errorData["message"] = $e->getMessage();
			$errorData["specific"] =  "in " . $e->getFile() . ":" . $e->getLine();
			$errorData["trace"] = $e->getTraceAsString();

			$errorData["query"] = "";
			if ($_ENV["SQL_ERROR"]) {
				$errorData["query"] = DBAccess::getInterpolatedQuery();
			}

			error_log($e);
		} else {
			if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
				$errorData["type"] = "Fatal error: " . $error['message'];
				$errorData["message"] = "";
				$errorData["specific"] =  "in " . $error['file'] . ":" . $error['line'];
				$errorData["trace"] = "";
				$errorData["query"] = "";
			}
		}

		$errorTempalte = TemplateController::getTemplate("error", [
			"errorData" => $errorData,
		]);
	}

	insertTemplate("./files/layout/footer.php", [
		"calcDuration" => $_ENV["DEV_MODE"],
		"errorTemplate" => $errorTempalte,
	]);
}

register_shutdown_function("fatal_handler");

/**
 * https://stackoverflow.com/questions/2236668/file-get-contents-breaks-up-utf-8-characters
 */
function file_get_contents_utf8($fn)
{
	$content = file_get_contents($fn);
	return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

/**
 * polyfill str_contains, maybe remove it later when support for older version drops
 * https://www.php.net/manual/en/function.str-contains.php
 */
if (!function_exists('str_contains')) {
	function str_contains($haystack, $needle): bool
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}

function getCurrentVersion(): string
{
	return CURRENTVERSION;
}

function errorReporting(): void
{
	if ($_ENV["ERRORREPORTING"] == "on") {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', 0);
	}
}

function insertTemplate($path, array $parameters = [])
{
	if (file_exists($path)) {
		extract($parameters);
		include $path;
	}
}

/** https://stackoverflow.com/a/2792045/7113688 */
function dashesToCamelCase($string, $capitalizeFirstCharacter = false): string
{
	$str = str_replace('-', '', ucwords($string, '-'));

	if (!$capitalizeFirstCharacter) {
		$str = lcfirst($str);
	}

	return $str;
}
