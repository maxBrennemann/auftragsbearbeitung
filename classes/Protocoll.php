<?php

class Protocoll {
	private $file;
	
	function __construct() {
		$this->file = fopen("protocoll.txt", "a");
	}
	
	public function writeToFile($text) {
		$text = $text . "\n"; // get into the next line
		fwrite($this->file, $text);
	}

	public static function prettyPrint($data) {
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}
}

?>