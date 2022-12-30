<?php

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

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
		//MinifyFiles::minify();
		$fileName = explode(".", $script);

		if (sizeof($fileName) == 2) {
			$min = "min/" . $fileName[0] . ".min.js";
			if (file_exists(Link::getResourcesLink($min, "js", false))) {
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

	if (sizeof($fileName) == 2) {
		$min = "min/" . $fileName[0] . ".min.css";
		if (file_exists(Link::getResourcesLink($min, "css", false))) {
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

/* https://stackoverflow.com/questions/15774669/list-all-files-in-one-directory-php */
class MinifyFiles {

	private static function getJs() {
		$path    = 'files/res/js/';
		$files = scandir($path);
		$files = array_diff(scandir($path), array('.', '..'));
		return $files;
	}

	private static function getCss() {
		$path    = 'files/res/css/';
		$files = scandir($path);
		$files = array_diff(scandir($path), array('.', '..'));
		return $files;
	}

	private static function minifyByType($files) {
		foreach ($files as $file) {
			$name = explode("/", $file);
			$name = $name[array_key_last($name)];
	
			$name = explode(".", $name);
			if (sizeof($name) > 1) {
				$type = $name[array_key_last($name)];
				$name = $name[array_key_last($name) - 1];

				switch ($type) {
					case "js":
						$sourcePath = "files/res/js/" . $file;
						$minifier = new JS($sourcePath);
						$minifiedPath = 'files/res/js/min/' . $name . ".min.js";
						$minifier->minify($minifiedPath);
						break;
					case "css":
						$sourcePath = "files/res/css/" . $file;
						$minifier = new CSS($sourcePath);
						$minifiedPath = 'files/res/css/min/' . $name . ".min.css";
						$minifier->minify($minifiedPath);
						break;
				}
			}
		}
	}
	
	public static function minify() {
		$filesJs = self::getJs();
		$filesCss = self::getCss();

		self::minifyByType($filesJs);
		self::minifyByType($filesCss);
	}

}

?>