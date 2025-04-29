<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\DBAccess;

class Link
{

	public $baseLink;
	public $key;
	public $data;
	public $datakey;

	public function __construct() {}

	public static function getPageLink($resourceName)
	{
		if (DBAccess::selectQuery("SELECT * FROM articles WHERE src = '$resourceName'") == null) {
			return self::get404();
		}

		$link = $_ENV["WEB_URL"] . $_ENV["SUB_URL"] . $resourceName;
		return $link;
	}

	public static function get404()
	{
		$link = $_ENV["WEB_URL"] . $_ENV["SUB_URL"] . "404";
		return $link;
	}

	/**
	 * function returns the link to the image resource;
	 * if the resource does not exist, the default image is returned
	 * 
	 * @param $resourceName: the name of the image resource
	 * @return $link: the link to the image resource
	 */
	public static function getImageLink($resourceName)
	{
		if ($resourceName == null || $resourceName == "" || !file_exists("files/res/image/" . $resourceName)) {
			$resourceName = "default_image.png";
		}

		if ($resourceName == "default_image.png") {
			$link = $_ENV["REWRITE_BASE"] . "img/" . $resourceName;
			return $link;
		}

		$link = $_ENV["REWRITE_BASE"] . "files/res/image/" . $resourceName;
		return $link;
	}

	public static function getResourcesLink($resource, $type, $rewriteBase = true)
	{
		if ($rewriteBase) {
			$rewriteBase = $_ENV["REWRITE_BASE"];
		} else {
			$rewriteBase = "";
		}
		switch ($type) {
			case "css":
				$link = $rewriteBase . "files/res/css/" . $resource;
				break;
			case "js":
				$link = $rewriteBase . "files/res/js/" . $resource;
				break;
			case "font":
				$link = $rewriteBase . "files/res/font/" . $resource;
				break;
			case "html":
				$link = $rewriteBase . "files/res/form/" . $resource;
				break;
			case "csv":
				$link = $rewriteBase . "files/generated/fb_export/" . $resource;
				break;
			case "upload":
				$subDir = substr($resource, 0, 2). "/" . substr($resource, 2, 2);
				$link = $rewriteBase . "upload/" . $subDir . "/" . $resource;
				break;
			case "backup":
				$link = $rewriteBase . "files/generated/sql_backups/" . $resource;
				break;
			case "pdf":
				$link = $rewriteBase . "files/generated/invoice/" . $resource;
				break;
		}

		return $link;
	}

	public static function getResourcesShortLink($resource, $type)
	{
		switch ($type) {
			case "css":
				$link = $_ENV["REWRITE_BASE"] . "css/" . $resource;
				break;
			case "js":
				$resource = dashesToCamelCase($resource);

				if (MINIFY_STATUS) {
					$resourceMin = str_replace(".js", ".", $resource);
					$files = scandir("files/res/js/min/");
					foreach ($files as $file) {
						if (str_starts_with($file, $resourceMin) !== false) {
							$name = basename($file);
							$name = explode(".", $name);
							$link = $_ENV["REWRITE_BASE"] . "js/" . $resourceMin . $name[1] . ".js";
						}
					}
					if (!isset($link)) {
						$link = $_ENV["REWRITE_BASE"] . "js/" . $resource;
					}
				} else {
					$link = $_ENV["REWRITE_BASE"] . "js/" . $resource;
				}
				break;
			case "extJs":
				/* extJs is for external js files, therefoe the fileSrc table column is returned ($resource) */
				$link = $resource;
				break;
			case "font":
				$link = $_ENV["REWRITE_BASE"] . "font/" . $resource;
				break;
			case "upload":
				$link = $_ENV["REWRITE_BASE"] . "upload/" . $resource;
				break;
			case "img":
				$link = $_ENV["REWRITE_BASE"] . "img/" . $resource;
				break;
			case "backup":
				$link = $_ENV["REWRITE_BASE"] . "backup/" . $resource;
				break;
			case "pdf":
				$link = $_ENV["REWRITE_BASE"] . "pdf_invoice/" . $resource;
				break;
		}

		return $link;
	}

	public static function getGlobalCSS()
	{
		return self::getResourcesShortLink("global.css", "css");
	}

	public static function getTW()
	{
		return self::getResourcesShortLink("tailwind.css", "css");
	}

	public static function getGlobalJS()
	{
		return self::getResourcesShortLink("global.js", "js");
	}

	/* new link functionalities */
	public function addBaseLink($target)
	{
		$this->baseLink = self::getPageLink($target);
	}

	public function addParameter($key, $value)
	{
		return $this->baseLink . "?$key=$value";
	}

	public function setIterator($key, $data, $datakey)
	{
		$this->key = $key;
		$this->data = $data;
		$this->datakey = $datakey;
	}

	public function getLink($id)
	{
		return self::addParameter($this->key, $this->data[$id][$this->datakey]);
	}
}
