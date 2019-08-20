<!DOCTPE html>
<html>
<head>
	<base href="https://www.google.com/" target="_blank">
</head>
<body>
<?php
	$content = file_get_contents('https://www.google.com');
	$UTFcontent = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	//echo $UTFcontent;

	$baseURL;
	
	$document = new \DOMDocument('1.0', 'UTF-8');
	$internalErrors = libxml_use_internal_errors(true);
	$document->loadHTML($content);
	libxml_use_internal_errors($internalErrors);
	
	//$dom = DOMDocument::loadHTML($content);
	
	$arr = $document->getElementsByTagName('base');
	foreach($arr as $arrs) {
		echo $arrs;//->getAttribute('href');
		var_dump($arrs);//->getAttribute('href');
	}
?>
</body>
</html>