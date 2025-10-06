<?php

namespace Src\Classes;

use Src\Classes\Project\Config;

class Link
{
    public string $baseLink;
    public string $key;
    /** @var array<mixed, mixed> */
    public array $data;
    public string $datakey;

    public function __construct() {}

    public static function getPageLink(string $resourceName): string
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
        return $_ENV["REWRITE_BASE"] . "public/assets/img/default_image.png";
    }

    /**
     * Returns the file name by resource name and type
     * @param string $resource
     * @param string $type
     * @return string
     */
    public static function getFilePath(string $resource, string $type): string
    {
        switch ($type) {
            case "css":
                $link = "public/res/css/" . $resource;
                break;
            case "js":
            case "ts":
                $link = "public/res/js/" . $resource;
                break;
            case "min":
                $link = "public/res/assets/" . $resource;
                break;
            case "font":
                $link = "public/res/css/fonts/" . $resource;
                break;
            case "html":
                $link = "public/assets/forms/" . $resource;
                break;
            case "upload":
                $subDir = substr($resource, 0, 2) . "/" . substr($resource, 2, 2);
                $link = Config::get('paths.uploadDir.default') . $subDir . "/" . $resource;
                break;
            case "csv":
            case "backup":
            case "pdf":
                $link = Config::get('paths.generatedDir') . $resource;
                break;
            default:
                $link = "";
        }

        return $link;
    }

    /**
     * Generates a short link for resources, so that the file path is not visible in the frontend
     * @param string $resource
     * @param string $type
     */
    public static function getResourcesShortLink(string $resource, string $type): string
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

    public static function getGlobalCSS(): string
    {
        $file = ResourceManager::getFileNameWithHash("global.js", "css");
        return self::getResourcesShortLink($file, "css");
    }

    public static function getGlobalJS(): string
    {
        $file = ResourceManager::getFileNameWithHash("global.js");
        return self::getResourcesShortLink($file, "js");
    }

    public function addBaseLink(string $target): void
    {
        $this->baseLink = self::getPageLink($target);
    }

    public function addParameter(string $key, string $value): string
    {
        return $this->baseLink . "?$key=$value";
    }

    /**
     * @param string $key
     * @param array<mixed, mixed> $data
     * @param string $datakey
     * @return void
     */
    public function setIterator(string $key, array $data, string $datakey): void
    {
        $this->key = $key;
        $this->data = $data;
        $this->datakey = $datakey;
    }

    public function getLink(int $id): string
    {
        return self::addParameter($this->key, $this->data[$id][$this->datakey]);
    }
}
