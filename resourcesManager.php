<?php

require_once('settings.php');
require_once('classes/MinifyFiles.php');
require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');

$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = explode('/', $requestUri);

$type = $requestUri[1];
$pathToResource = implode('/', array_slice($requestUri, 0, 2));
$resource = str_replace($pathToResource, "", $_SERVER['REQUEST_URI']);
$resource = explode('?', $resource)[0];

if ($resource == "") {
	http_response_code(404);
	exit();
}

switch ($type) {
	case "js":
		get_script($resource);
		break;
	case "css":
		get_css($resource);
		break;
	case "font":
		get_font($resource);
		break;
	case "pdf_invoice":
		get_pdf_invoice($resource);
		break;
	case "upload":
		get_upload($resource);
		break;
	case "backup":
		get_backup($resource);
		break;
	case "static":
		get_static($resource);
		break;
	case "img":
		get_image($resource);
		break;
	default:
		http_response_code(404);
		exit();
}

function get($type) {
	return isset($_GET[$type]) ? $_GET[$type] : null;
}

function get_script($script) {
	header('Content-Type: text/javascript');

	if ($script == "colorpicker.js") {
		$file = file_get_contents(".res/colorpicker.js");
	} else {
		$fileName = explode(".", $script);

		if (sizeof($fileName) == 2) {
			$min = "min/" . $fileName[0] . ".min.js";
			if (file_exists(Link::getResourcesLink($min, "js", false)) && MinifyFiles::isActivated()) {
				$file = file_get_contents(Link::getResourcesLink($min, "js", false));
			} else {
				if (file_exists(Link::getResourcesLink($script, "js", false))) {
					$file = file_get_contents(Link::getResourcesLink($script, "js", false));
				} else {
					$file = "";
				}
			}
		} else {
			$file = "";
		}
	}

	echo $file;
}

function get_css($script) {
	header("Content-type: text/css");
	$fileName = explode(".", $script);

	/* quick workaround for font files accessed via css/font/ */
	$font = checkFont($fileName);
	if ($font != false) {
		get_font($font);
		return;
	}

	if (sizeof($fileName) == 2) {
		$min = "min/" . $fileName[0] . ".min.css";
		if (file_exists(Link::getResourcesLink($min, "css", false)) && MinifyFiles::isActivated()) {
			$file = file_get_contents(Link::getResourcesLink($min, "css", false));
		} else {
			if (file_exists(Link::getResourcesLink($script, "css", false))) {
				$file = file_get_contents(Link::getResourcesLink($script, "css", false));
			} else {
				$file = "";
			}
		}
	} else {
		$file = "";
	}

	echo $file;
}

function checkFont($fileName) {
	$len = count($fileName);
	$last = $fileName[$len - 1];
	
	if ($last == "ttf") {
		$names = explode("/", $fileName[0]);
		$len = count($names);
		$last = $names[$len - 1];
		return $last . ".ttf";
	}

	return false;
}

function get_font($font) {
	header("Content-type: font/ttf");
	$file = file_get_contents(Link::getResourcesLink($font, "font", false));

	echo $file;
}

function get_upload($upload) {
	$file_info = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($upload, "upload", false)));
	
	header("Content-type:$mime_type");
	$file = file_get_contents(Link::getResourcesLink($upload, "upload", false));

	echo $file;
}

function get_backup($backup) {
	$file_info = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($backup, "backup", false)));
	
	header("Content-type:$mime_type");
	$file = file_get_contents(Link::getResourcesLink($backup, "backup", false));

	echo $file;
}

function get_pdf_invoice($pdf) {
	header("Content-type: application/pdf");
	$file = file_get_contents(Link::getResourcesLink($pdf, "pdf", false));

	echo $file;
}

function get_image($file) {
	header("Content-type: " .  mime_content_type("img/" . $file));
	$file = file_get_contents("img/" . $file);

	echo $file;
}

function get_static($file) {
	if ($file == "facebook-product-export") {
		header("Content-type: text/csv");
		$filename = "exportFB_" . date("Y-m-d") . ".csv";
		$file = file_get_contents(Link::getResourcesLink($filename, "csv", false));
		echo $file;
		// TODO: check if file exists and if not, return latest file
	} else if ($file == "generate-facebook") {
		require_once("classes/project/modules/sticker/exports/ExportFacebook.php");

		$exportFacebook = new ExportFacebook();
		$exportFacebook->generateCSV();
	}
}
