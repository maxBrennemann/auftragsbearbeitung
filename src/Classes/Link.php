<?php

namespace Src\Classes;

use Src\Classes\Project\Config;

class Link
{

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
                $link = ROOT . "public/res/css/" . $resource;
                break;
            case "ts":
                $link = ROOT . "public/res/js/pages/" . $resource;
                break;
            case "min":
                $link = ROOT . "public/res/assets/" . $resource;
                break;
            case "font":
                $link = ROOT . "public/res/css/fonts/" . $resource;
                break;
            case "html":
                $link = ROOT . "public/assets/forms/" . $resource;
                break;
            case "upload":
                $link = self::getUploadFileByURL($resource);
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

    private static function getUploadFileByURL(string $resource): string
    {
        $resourceParts = explode("/", $resource);
        if (count($resourceParts) >= 3) {
            throw new \Exception("Unsupported resource path.");
        }

        if (count($resourceParts) == 2) {
            $resource = $resourceParts[0];
        }

        $subDir = substr($resource, 0, 2) . "/" . substr($resource, 2, 2);
        return Config::get('paths.uploadDir.default') . $subDir . "/" . $resource;
    }

    /**
     * Links are generated for the html output, they will be parsed by ResourceManager.php
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

    public static function getUploadResourceLink(string $resource, string $originalname): string
    {
        $nameparts = explode(".", $resource, 1);

        if (count($nameparts) == 1) {
            return $_ENV["REWRITE_BASE"] . "upload/" . $resource . "/" . $originalname;
        }

        return $_ENV["REWRITE_BASE"] . "upload/" . $resource . "/" . $originalname;
    }

    public static function getGlobalCSS(): string
    {
        $file = ResourceManager::getFileNameWithHash("global.js", "css");
        return self::getResourcesShortLink($file, "css");
    }

    public static function getGlobalScript(): string
    {
        $file = ResourceManager::getFileNameWithHash("global.js");
        return self::getResourcesShortLink($file, "js");
    }
}
