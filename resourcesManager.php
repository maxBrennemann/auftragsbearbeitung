<?php

require_once('settings.php');
require_once('classes/MinifyFiles.php');
require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');

function get($type) {
	return isset($_GET[$type]) ? $_GET[$type] : null;
}

$types = [
	"script",
	"css",
	"font",
	"upload",
	"backup",
	"pdf_invoice",
	"static",
];

foreach ($types as $t) {
	$val = get($t);
	if ($val != null) {
		call_user_func("get_" . $t, $val);
		break;
	}
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

function get_static($file) {
	if ($file == "facebook-product-export") {
		header("Content-type: text/csv");
		$filename = "exportFB_" . date("Y-m-d") . ".csv";
		$file = file_get_contents(Link::getResourcesLink($filename, "csv"));
		echo $file;
		// TODO: check if file exists and if not, return latest file
	} else if ($file == "generate-facebook") {
		require_once("classes/project/modules/sticker/exports/ExportFacebook.php");

		$exportFacebook = new ExportFacebook();
		$exportFacebook->generateCSV();
	}
}

?>