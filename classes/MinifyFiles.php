<?php

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

/* https://stackoverflow.com/questions/15774669/list-all-files-in-one-directory-php */
class MinifyFiles {

	private static function getJs() {
		$path = 'files/res/js/';
		$files = scandir($path);
		$files = array_diff(scandir($path), array('.', '..'));
		return $files;
	}

	private static function getCss() {
		$path = 'files/res/css/';
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

    public static function isActivated() {
        $query = "SELECT content FROM settings WHERE title = 'minifyStatus' LIMIT 1";
        $result = DBAccess::selectQuery($query);
        $minifyStatus = $result[0]["content"];
        if ($minifyStatus == "on") {
            return true;
        }
        return false;
    }

}

?>