<?php

namespace Classes;

class Link
{
    public $baseLink;
    public $key;
    public $data;
    public $datakey;

    public function __construct()
    {
    }

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
     * @return string
     */
    public static function getDefaultImage(): string
    {
        return $_ENV["REWRITE_BASE"] . "files/assets/img/default_image.png";
    }

    /**
     * Returns the file name by resource name and type
     * @param mixed $resource
     * @param mixed $type
     * @return string
     */
    public static function getFilePath($resource, $type): string
    {
        switch ($type) {
            case "css":
                $link = "files/res/css/" . $resource;
                break;
            case "js":
            case "ts":
                $link = "files/res/js/" . $resource;
                break;
            case "min":
                $link = "files/res/assets/" . $resource;
                break;
            case "font":
                $link = "files/res/css/fonts/" . $resource;
                break;
            case "html":
                $link = "files/assets/forms/" . $resource;
                break;
            case "upload":
                $subDir = substr($resource, 0, 2) . "/" . substr($resource, 2, 2);
                $link = "upload/" . $subDir . "/" . $resource;
                break;
            case "csv":
            case "backup":
            case "pdf":
                $link = "generated/" . $resource;
                break;
            default:
                $link = "";
        }

        return $link;
    }

    /**
     * Generates a short link for resources, so that the file path is not visible in the frontend
     * @param mixed $resource
     * @param mixed $type
     */
    public static function getResourcesShortLink($resource, $type)
    {
        switch ($type) {
            case "css":
                $link = $_ENV["REWRITE_BASE"] . "css/" . $resource;
                break;
            case "js":
                $link = $_ENV["REWRITE_BASE"] . "js/" . $resource;
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
            default:
                $link = "";
        }

        return $link;
    }

    public static function getGlobalCSS()
    {
        $file = ResourceManager::getFileNameWithHash("global.js", "css");
        return self::getResourcesShortLink($file, "css");
    }

    public static function getGlobalJS()
    {
        $file = ResourceManager::getFileNameWithHash("global.js");
        return self::getResourcesShortLink($file, "js");
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
