<?php

namespace Classes;

class Files {

    public static function get_file_contents($filename) {
		$file = Link::getResourcesLink($filename, "html", false);
		return file_get_contents_utf8($file);
	}

	public static function get_file_contents_by_file_name($filename) {
		$file = Link::getResourcesLink($filename . ".htm", "html", false);
		return file_get_contents_utf8($file);
    }
    
}
