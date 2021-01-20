<?php
	require_once('settings.php');
	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');

	if(isset($_GET['script'])) {
		header('Content-Type: text/javascript');
		$script = $_GET['script'];
		if ($script == "colorpicker.js") {
			$file = file_get_contents(".res/colorpicker.js");
		} else {
			$file = file_get_contents(Link::getResourcesLink($script, "js", false));
		}
		echo $file;
	}

	if(isset($_GET['css'])) {
		header("Content-type: text/css");
		$script = $_GET['css'];
		$file = file_get_contents(Link::getResourcesLink($script, "css", false));
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

	if (isset($_GET['pdf_invoice'])) {
		header("Content-type: application/pdf");
		$pdf = $_GET['pdf_invoice'];
		$file = file_get_contents(Link::getResourcesLink($pdf, "pdf", false));
		echo $file;
	}

?>