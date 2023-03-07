<?php

require_once('classes/MinifyFiles.php');
require_once('vendor/autoload.php');
require_once('settings.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');

if (isset($_GET['script'])) {
	header('Content-Type: text/javascript');
	$script = $_GET['script'];

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

if (isset($_GET['css'])) {
	header("Content-type: text/css");
	$script = $_GET['css'];
	$fileName = explode(".", $script);

	if ($fileName[0] == "tw") {
		$file = file_get_contents(Link::getResourcesLink("min/t-style.min.css", "css", false));
		echo $file;
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

if (isset($_GET['font'])) {
	header("Content-type: font/ttf");
	$script = $_GET['font'];
	$file = file_get_contents(Link::getResourcesLink($script, "font", false));

	echo $file;
}

if (isset($_GET['upload'])) {
	$file_info = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($_GET['upload'], "upload", false)));
	
	header("Content-type:$mime_type");
	$file = file_get_contents(Link::getResourcesLink($_GET['upload'], "upload", false));

	echo $file;
}

if (isset($_GET['backup'])) {
	$file_info = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($_GET['backup'], "backup", false)));
	
	header("Content-type:$mime_type");
	$file = file_get_contents(Link::getResourcesLink($_GET['backup'], "backup", false));

	echo $file;
}

if (isset($_GET['pdf_invoice'])) {
	header("Content-type: application/pdf");
	$pdf = $_GET['pdf_invoice'];
	$file = file_get_contents(Link::getResourcesLink($pdf, "pdf", false));

	echo $file;
}

?>