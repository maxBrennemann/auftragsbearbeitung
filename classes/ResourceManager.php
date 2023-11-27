<?php

require_once('classes/MinifyFiles.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');
require_once('classes/project/Config.php');
require_once('classes/Ajax.php');
require_once('classes/Link.php');
require_once('classes/Login.php');
require_once('classes/Protocol.php');
require_once('classes/project/FormGenerator.php');
require_once('classes/project/CacheManager.php');
require_once('classes/project/Icon.php');
require_once('classes/project/Posten.php');
require_once('classes/project/Angebot.php');
require_once('classes/project/NotificationManager.php');

class ResourceManager {

    function __construct() {
        
    }

    /**
     * Before: page was submitted via $_GET paramter, but now the REQUEST_URI is read;
     * $url is splitted into the REQUEST_URI and the parameter part
     */
    public static function pass() {
        $url = $_SERVER['REQUEST_URI'];
        $url = explode('?', $url, 2);
        $page = str_replace($_ENV["REWRITE_BASE"] . $_ENV["SUB_URL"], "", $url[0]);
        $parts = explode('/', $page);
        $page = $parts[count($parts) - 1];

        switch ($parts[1]) {
            case "js":
            case "css":
            case "font":
            case "pdf_invoice":
            case "upload":
            case "img":
                self::handleResources();
                exit;
            case "api":
                Ajax::manageRequests($_POST['getReason'], $page);
                exit;
            case "admin":
                require_once('admin.php');
                exit;
            case "account":
                require_once('account.php');
                exit;
            case "shop":
                require_once('frontOfficeController.php');
                exit;
            case "upgrade":
                require_once('upgrade.php');
                exit;
            case "favicon.ico":
                require_once('favicon.php');
                exit;
        }
    }

    public static function session() {
        session_start();
        errorReporting();
    }

    private static function handleResources() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = explode('/', $requestUri);

        $type = $requestUri[1];
        $pathToResource = implode('/', array_slice($requestUri, 0, 2));
        $resource = str_replace($pathToResource, "", $_SERVER['REQUEST_URI']);
        $resource = explode('?', $resource)[0];

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
            case "pdf_invoice":
                self::get_pdf_invoice($resource);
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

    private static function get($type) {
        return isset($_GET[$type]) ? $_GET[$type] : null;
    }

    private static function get_script($script) {
        header('Content-Type: text/javascript');

        if ($script == "colorpicker.js") {
            $file = file_get_contents(".res/colorpicker.js");
        } else {
            $fileName = explode(".", $script);

            if (sizeof($fileName) == 2) {
                $min = "min/" . $fileName[0] . ".min.js";
                if (file_exists(Link::getResourcesLink($min, "js", false)) && MinifyFiles::isActivated()) {
                    $file = file_get_contents(Link::getResourcesLink($min, "js", false));
                } else {
                    if (file_exists(Link::getResourcesLink($script, "js", false))) {
                        $file = file_get_contents(Link::getResourcesLink($script, "js", false));
                    } else {
                        $file = "";
                    }
                }
            } else {
                $file = "";
            }
        }

        echo $file;
    }

    private static function get_css($script) {
        header("Content-type: text/css");
        $fileName = explode(".", $script);

        /* quick workaround for font files accessed via css/font/ */
        $font = self::checkFont($fileName);
        if ($font != false) {
            self::get_font($font);
            return;
        }

        if (sizeof($fileName) == 2) {
            $min = "min/" . $fileName[0] . ".min.css";
            if (file_exists(Link::getResourcesLink($min, "css", false)) && MinifyFiles::isActivated()) {
                $file = file_get_contents(Link::getResourcesLink($min, "css", false));
            } else {
                if (file_exists(Link::getResourcesLink($script, "css", false))) {
                    $file = file_get_contents(Link::getResourcesLink($script, "css", false));
                } else {
                    $file = "";
                }
            }
        } else {
            $file = "";
        }

        echo $file;
    }

    private static function checkFont($fileName) {
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

    private static function get_font($font) {
        header("Content-type: font/ttf");
        $file = file_get_contents(Link::getResourcesLink($font, "font", false));

        echo $file;
    }

    private static function get_upload($upload) {
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($upload, "upload", false)));
        
        header("Content-type:$mime_type");
        $file = file_get_contents(Link::getResourcesLink($upload, "upload", false));

        echo $file;
    }

    private static function get_backup($backup) {
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($backup, "backup", false)));
        
        header("Content-type:$mime_type");
        $file = file_get_contents(Link::getResourcesLink($backup, "backup", false));

        echo $file;
    }

    private static function get_pdf_invoice($pdf) {
        header("Content-type: application/pdf");
        $file = file_get_contents(Link::getResourcesLink($pdf, "pdf", false));

        echo $file;
    }

    private static function get_image($file) {
        header("Content-type: " .  mime_content_type("img/" . $file));
        $file = file_get_contents("img/" . $file);

        echo $file;
    }

    private static function get_static($file) {
        if ($file == "facebook-product-export") {
            header("Content-type: text/csv");
            $filename = "exportFB_" . date("Y-m-d") . ".csv";
            $file = file_get_contents(Link::getResourcesLink($filename, "csv", false));
            echo $file;
            // TODO: check if file exists and if not, return latest file
        } else if ($file == "generate-facebook") {
            require_once("classes/project/modules/sticker/exports/ExportFacebook.php");

            $exportFacebook = new ExportFacebook();
            $exportFacebook->generateCSV();
        }
    }

}
