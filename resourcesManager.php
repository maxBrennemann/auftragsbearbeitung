<?php

require_once('vendor/autoload.php');
require_once('settings.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');

if(isset($_GET['script'])) {
	header('Content-Type: text/javascript');
	$script = $_GET['script'];
	if ($script == "colorpicker.js") {
		$file = file_get_contents(".res/colorpicker.js");
	} else {
		if (file_exists(Link::getResourcesLink($script, "js", false))) {
			$file = file_get_contents(Link::getResourcesLink($script, "js", false));
		} else {
			$file = "";
		}
	}
	echo $file;
}

if(isset($_GET['css'])) {
	header("Content-type: text/css");
	$script = $_GET['css'];
	$file = file_get_contents(Link::getResourcesLink($script, "css", false));
	if ($file == false)
		return "";
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

/*class Minify {

	use MatthiasMullie\Minify;
	
	public function minify() {
		$sourcePath = '/path/to/source/css/file.css';
		$minifier = new CSS($sourcePath);

		// we can even add another file, they'll then be
		// joined in 1 output file
		$sourcePath2 = '/path/to/second/source/css/file.css';
		$minifier->add($sourcePath2);

		// or we can just add plain CSS
		$css = 'body { color: #000000; }';
		$minifier->add($css);

		// save minified file to disk
		$minifiedPath = '/path/to/minified/css/file.css';
		$minifier->minify($minifiedPath);

		// or just output the content
		echo $minifier->minify();
	}

}*/

?>