<?php

namespace Classes;

class Link
{

	public $baseLink;
	public $key;
	public $data;
	public $datakey;

	public function __construct() {}

	public static function getPageLink($resourceName): string
	{
		$link = $_ENV["WEB_URL"] . $_ENV["SUB_URL"] . $resourceName;
		return $link;
	}

	/**
	 * function returns the link to the image resource;
	 * if the resource does not exist, the default image is returned
	 * 
	 * @param $resourceName: the name of the image resource
	 * @return $link: the link to the image resource
	 */
	public static function getDefaultImage(): string
	{
		return $_ENV["REWRITE_BASE"] . "files/assets/img/default_image.png";
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
				$link = $rewriteBase . "files/res/assets/" . $resource;
				break;
			case "js":
				$link = $rewriteBase . "files/res/assets/" . $resource;
				break;
			case "font":
				$link = $rewriteBase . "files/res/css/fonts/" . $resource;
				break;
			case "html":
				$link = $rewriteBase . "files/assets/forms/" . $resource;
				break;
			case "upload":
				$subDir = substr($resource, 0, 2) . "/" . substr($resource, 2, 2);
				$link = $rewriteBase . "upload/" . $subDir . "/" . $resource;
				break;
			case "csv":
			case "backup":
			case "pdf":
				$link = $rewriteBase . "generated/" . $resource;
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
				$link = $_ENV["REWRITE_BASE"] . "pdfs/" . $resource;
				break;
		}

		return $link;
	}

	public static function getGlobalCSS()
	{
		return self::getResourcesShortLink("global.css", "css");
	}

	public static function getGlobalJS()
	{
		return self::getResourcesShortLink("main.js", "js");
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
