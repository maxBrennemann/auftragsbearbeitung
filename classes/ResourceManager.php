<?php

namespace Classes;

use Classes\Controller\SessionController;
use Classes\Project\CacheManager;
use Classes\Project\Events;
use Classes\Sticker\Exports\ExportFacebook;
use Classes\Sticker\Imports\ImportGoogleSearchConsole;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

class ResourceManager
{
    private static $page = "";
    private static $type = "";

    public static function initialize()
    {
        $companyName = DBAccess::selectQuery("SELECT content FROM settings WHERE title = 'companyName';");
        if ($companyName != null) {
            define("COMPANY_NAME", $companyName[0]["content"]);
        } else {
            define("COMPANY_NAME", "Auftragsbearbeitung");
        }

        errorReporting();
    }

    public static function identifyRequestType(): void
    {
        $url = $_SERVER["REQUEST_URI"];
        $url = explode('?', $url, 2);
        $page = str_replace($_ENV["REWRITE_BASE"] . $_ENV["SUB_URL"], "", $url[0]);
        $parts = explode('/', $page);

        self::$page = $parts[count($parts) - 1];
        self::$type = $parts[1];
    }

    /**
     * There are three possible request types needed for the SessionController response;
     * @return string
     */
    public static function getRequestType(): string
    {
        switch (self::$type) {
            case "js":
            case "css":
            case "font":
            case "pdfs":
            case "upload":
            case "backup":
            case "img":
            case "static":
            case "favicon.ico":
            case "events":
                return "resource";
            case "api":
                return "api";
            default:
                return "page";
        }
    }

    public static function pass(): void
    {
        switch (self::$type) {
            case "js":
            case "css":
            case "font":
            case "pdfs":
            case "upload":
            case "backup":
            case "img":
            case "static":
                self::handleResources();
                self::close();
                // no break
            case "api":
                Ajax::handleRequests();
                self::close();
                // no break
            case "favicon.ico":
                require_once "files/assets/favicon.php";
                self::close();
                // no break
            case "events":
                Events::init();
                self::close();
                // @phpstan-ignore-next-line
                break;
        }
    }

    public static function getParameters()
    {
        if (file_get_contents("php://input") != "") {
            $PHP_INPUT = json_decode(file_get_contents("php://input"), true);

            if ($PHP_INPUT != null) {
                Tools::$data = array_merge(Tools::$data, $PHP_INPUT);
                $_POST = array_merge($_POST, $PHP_INPUT);
            }
        }

        switch ($_SERVER["REQUEST_METHOD"]) {
            case "POST":
                Tools::$data = array_merge(Tools::$data, $_POST);
                break;
            case "GET":
                Tools::$data = array_merge(Tools::$data, $_GET);
                break;
            case "PUT":
            case "DELETE":
                /* https://stackoverflow.com/questions/20320634/how-to-get-put-delete-arguments-in-php */
                parse_str(file_get_contents("php://input"), $_PUT);
                Tools::$data = array_merge(Tools::$data, $_PUT);
                break;
        }
    }

    public static function setPage(string $page): void
    {
        self::$page = $page;
    }

    public static function initPage()
    {
        if (!SessionController::isLoggedIn()) {
            self::$page = "login";
            self::showPage();
            return;
        }

        $getReason = Tools::get("getReason");

        /* filters AJAX requests and delegates them to the right files */
        if ($getReason != null) {
            Ajax::manageRequests($getReason, self::$page);
        } else {
            self::showPage();
        }
    }

    public static function showPage(): void
    {
        $routes = require "helpers/web-routes.php";
        $page = self::$page;

        $filePath = "";
        $pageName = "";

        if (isset($routes[$page])) {
            $filePath = $routes[$page]['file'];
            $pageName = $routes[$page]['name'] ?? ucfirst($page);
        } else {
            $candidateFile = "./files/pages/$page.php";

            if (file_exists($candidateFile)) {
                $filePath = "$page.php";
                $pageName = ucfirst(str_replace('-', ' ', $page));
            }
        }

        if (!$filePath || !file_exists("./files/pages/$filePath")) {
            http_response_code(404);
            $filePath = '404.php';
            $pageName = 'Page not found';
        }

        insertTemplate("./files/layout/header.php", [
            "pageName" => $pageName,
            "page" => $page,
            "jsPage" => $page == "" ? "home" : $page,
        ]);

        insertTemplate("./files/pages/$filePath");

        insertTemplate("./files/layout/footer.php", [
            "calcDuration" => $_ENV["DEV_MODE"],
        ]);
    }

    public static function close(): never
    {
        Protocol::close();
        DBAccess::close();
        exit;
    }

    private static function handleResources()
    {
        $requestUri = $_SERVER["REQUEST_URI"];
        $requestUri = explode("/", $requestUri);

        $type = $requestUri[1];
        $pathToResource = implode("/", array_slice($requestUri, 0, 2));
        $resource = str_replace($pathToResource, "", $_SERVER["REQUEST_URI"]);
        $resource = explode("?", $resource)[0];

        if ($resource == "") {
            http_response_code(404);
            exit();
        }

        switch ($type) {
            case "js":
                self::get_script($resource);
                break;
            case "css":
                self::get_css($resource);
                break;
            case "font":
                self::get_font($resource);
                break;
            case "pdfs":
                self::get_pdf($resource);
                break;
            case "upload":
                self::get_upload($resource);
                break;
            case "backup":
                self::get_backup($resource);
                break;
            case "static":
                self::get_static($resource);
                break;
            case "img":
                self::get_image($resource);
                break;
            default:
                http_response_code(404);
                exit();
        }
    }

    private static function get_script($script): void
    {
        header("Content-Type: text/javascript");

        if (file_exists(Link::getFilePath($script, "min"))) {
            echo file_get_contents(Link::getFilePath($script, "min"));
            return;
        }

        echo "";
    }

    private static function get_css($script)
    {
        header("Content-Type: text/css; charset=utf-8");

        $fileName = explode(".", $script);

        /* quick workaround for font files accessed via css/font/ */
        $font = self::checkFont($fileName);
        if ($font != false) {
            self::get_font($font);
            return;
        }

        if (file_exists(Link::getFilePath($script, "min"))) {
            echo file_get_contents(Link::getFilePath($script, "min"));
            return;
        }

        echo "";
    }

    public static function getFileNameWithHash(string $file, string $type = "file"): string
    {
        $json = @file_get_contents("./files/res/assets/.vite/manifest.json");

        if ($json == false) {
            return "";
        }

        $manifest = json_decode($json, true);
        if (!isset($manifest[$file])) {
            return $file;
        }

        if ($type !== "file") {
            return $manifest[$file][$type][0];
        }

        return $manifest[$file][$type];
    }

    private static function checkFont($fileName)
    {
        $len = count($fileName);
        $last = $fileName[$len - 1];

        if ($last == "ttf") {
            $names = explode("/", $fileName[0]);
            $len = count($names);
            $last = $names[$len - 1];
            return $last . ".ttf";
        }

        return false;
    }

    private static function get_font($font)
    {
        header("Content-type: font/ttf");
        $file = file_get_contents(Link::getFilePath($font, "font"));

        echo $file;
    }

    private static function get_upload($upload)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $upload = ltrim($upload, "/");
        $fileName = Link::getFilePath($upload, "upload");

        if (!file_exists($fileName)) {
            $mime_type = $finfo->file("files/assets/img/default_image.png");
            header("Content-type:$mime_type");
            echo file_get_contents("files/assets/img/default_image.png");
            return;
        }

        $mime_type = $finfo->file($fileName);

        $query = "SELECT originalname FROM dateien WHERE dateiname = :fileName LIMIT 1;";
        $response = DBAccess::selectQuery($query, [
            "fileName" => $upload,
        ]);
        if ($response != null) {
            $name = $response[0]["originalname"];
            header('Content-Disposition: filename="' . $name . '"');
        }

        header("Content-type:$mime_type");
        readfile($fileName);
    }

    private static function get_backup($backup)
    {
        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer(file_get_contents(Link::getFilePath($backup, "backup")));

        header("Content-type:$mime_type");
        $file = file_get_contents(Link::getFilePath($backup, "backup"));

        echo $file;
    }

    private static function get_pdf($pdf)
    {
        header("Content-type: application/pdf");
        $fileName = Link::getFilePath($pdf, "pdf");
        if (!file_exists($fileName)) {
            echo "";
            http_response_code(404);
            return;
        }

        $file = file_get_contents($fileName);

        echo $file;
    }

    private static function get_image($file)
    {
        $filePath = "files/assets/img" . $file;
        header("Content-type: " .  mime_content_type($filePath));
        $file = file_get_contents($filePath);

        echo $file;
    }

    private static function get_static($file)
    {
        if ($file == "facebook-product-export") {
            header("Content-type: text/csv");
            $filename = "exportFB_" . date("Y-m-d") . ".csv";
            $file = file_get_contents(Link::getFilePath($filename, "csv"));
            echo $file;
            // TODO: check if file exists and if not, return latest file
        } elseif ($file == "/generate-facebook") {
            $exportFacebook = new ExportFacebook();
            $exportFacebook->generateCSV();
        } elseif ($file == "/import-search-console") {
            ImportGoogleSearchConsole::import();
        }
    }

    public static function outputHeaderJSON()
    {
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header('Content-Type: application/json; charset=utf-8');
    }
}
