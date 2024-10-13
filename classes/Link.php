<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\DBAccess;

class Link
{

	public $baseLink;
	public $key;
	public $data;
	public $datakey;

	function __construct() {}

	public static function getPageLink($resourceName)
	{
		if (DBAccess::selectQuery("SELECT * FROM articles WHERE src = '$resourceName'") == null) {
			$link = $_ENV["WEB_URL"] . $_ENV["SUB_URL"] . "404";
			return $link;
		}

		$link = $_ENV["WEB_URL"] . $_ENV["SUB_URL"] . $resourceName;
		return $link;
	}

	public static function getFrontOfficeLink($page)
	{
		if (DBAccess::selectQuery("SELECT * FROM frontpage WHERE src = '$page'") == null) {
			$link = $_ENV["WEB_URL"] . $_ENV["FRONT"] . "";
			return $link;
		}

		$link = $_ENV["WEB_URL"] . $_ENV["FRONT"] . $page;
		return $link;
	}

	public static function getFrontOfficeName($page)
	{
		$data = DBAccess::selectQuery("SELECT pageName FROM frontpage WHERE src = '$page'");

		if ($data != null) {
			return $data[0]["pageName"];
		}

		return null;
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
				$link = $rewriteBase . "upload/" . $resource;
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
				$link = $_ENV["REWRITE_BASE"] . "js/" . $resource;
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

	/*
	 * function returns an array of link objects by breaking down the server uri
	 * variable.
	 * the links are representing the depth of the link
	 */
	public static function parseUri()
	{
		$url = $_SERVER["REQUEST_URI"];

		/* remove GET parameters */
		$url = explode("?", $url)[0];

		/* remove $_ENV["WEB_URL"] and $_ENV["FRONT"] */
		$url = str_replace($_ENV["WEB_URL"] . substr($_ENV["FRONT"], 0, -1), "", $url);

		$url_parts = explode("/", $url);

		$links = array();
		foreach ($url_parts as $u) {
			if (strcmp($u, $_ENV["WEB_URL"]) != 0) {
				$urlLink = new Link();
				$link = [
					"link" => Link::getFrontOfficeLink($u),
					"text" => Link::getFrontOfficeName($u)
				];
				array_push($links, $link);
			}
		}

		return $links;
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
