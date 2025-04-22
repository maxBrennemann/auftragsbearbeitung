<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\Posten;
use Classes\Project\Table;

use Classes\Project\Table\TableConfig;

use Classes\Project\Modules\Sticker\Exports\ExportFacebook;
use Classes\Project\Modules\Sticker\Imports\ImportGoogleSearchConsole;

use Classes\Project\Modules\Pdf\OfferPDF;
use Classes\Project\Modules\Pdf\InvoicePDF;

class ResourceManager
{

    private static $page = "";

    /**
     * Before: page was submitted via $_GET paramter, but now the REQUEST_URI is read;
     * $url is splitted into the REQUEST_URI and the parameter part
     */
    public static function pass()
    {
        $url = $_SERVER["REQUEST_URI"];
        $url = explode('?', $url, 2);
        $page = str_replace($_ENV["REWRITE_BASE"] . $_ENV["SUB_URL"], "", $url[0]);
        $parts = explode('/', $page);
        self::$page = $parts[count($parts) - 1];

        switch ($parts[1]) {
            case "js":
            case "css":
            case "font":
            case "pdf_invoice":
            case "upload":
            case "img":
            case "static":
                self::handleResources();
                self::close();
            case "api":
                Ajax::handleRequests();
                self::close();
            case "favicon.ico":
                require_once "favicon.php";
                exit;
        }
    }

    public static function session()
    {
        session_start();
        errorReporting();

        $query = "SELECT content FROM settings WHERE title = 'companyName';";
        $companyName = DBAccess::selectQuery($query);

        if ($companyName != null) {
            $_SESSION["companyName"] = $companyName[0]["content"];
        } else {
            $_SESSION["companyName"] = "Auftragsbearbeitung";
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

    public static function initPage()
    {
        $getReason = Tools::get("getReason");
        $isUpload = Tools::get("upload");

        /* filters AJAX requests and delegates them to the right files */
        if ($getReason != null) {
            Ajax::manageRequests($getReason, self::$page);
        } else if ($isUpload != null) {
            $uploadDestination = Tools::get("upload");

            /* checks which upload mechanism should be called */
            switch ($uploadDestination) {
                case "order":
                    $auftragsId = (int) Tools::get("auftrag");
                    $upload = new Upload();
                    $upload->uploadFilesAuftrag($auftragsId);
                    break;
                case "product":
                    $auftragsId = (int) Tools::get("produkt");
                    $upload = new Upload();
                    $upload->uploadFilesProduct($auftragsId);
                    break;
                case "postenAttachment":
                    $key = Tools::get("key");
                    $table = Tools::get("tableKey");
                    Posten::addFile($key, $table);
                    break;
                case "vehicle":
                    $key = Tools::get("key");
                    $table = Tools::get("tableKey");
                    $fahrzeugnummer = Table::getIdentifierValue($table, $key);

                    $auftragsnummer = Tools::get("orderid");
                    $upload = new Upload();
                    $upload->uploadFilesVehicle($fahrzeugnummer, $auftragsnummer);
                    break;
                case "motiv":
                    $motivname = Tools::get("motivname");
                    $upload = new Upload();

                    if (isset($_POST["motivNumber"])) {
                        $upload->uploadFilesMotive($motivname, $_POST["motivNumber"]);
                    } else {
                        $upload->uploadFilesMotive($motivname);
                    }
                    break;
            }
        } else {
            if (self::$page == "pdf") {
                $type = Tools::get("type");
                switch ($type) {
                    case "offer":
                        $offerId = (int) Tools::get("offerId");
                        $customerId = (int) Tools::get("customerId");
                        $offerPDF = new OfferPDF($offerId, $customerId);
                        $offerPDF->generate();
                        break;
                    case "invoice":
                        $invoiceId = (int) Tools::get("invoiceId");
                        $orderId = (int) Tools::get("orderId");
                        $invoice = new InvoicePDF($invoiceId, $orderId);
                        $invoice->generate();
                        break;
                }
            } else if (self::$page == "cron") {
                Ajax::manageRequests("testDummy", self::$page);
            } else if (isLoggedIn()) {
                self::showPage(self::$page);
            } else {
                self::showPage("login");
            }
        }
    }

    private static function showPage($page)
    {
        if ($page == "test") {
            return null;
        }

        $pageDetails = DBAccess::selectQuery("SELECT id, articleUrl, pageName FROM articles WHERE src = :page", [
            "page" => $page
        ]);
        $articleUrl = "";

        /* checks if file exists */
        if ($pageDetails == null || !file_exists("./files/" . $pageDetails[0]["articleUrl"])) {
            http_response_code(404);

            $baseUrl = 'files/';
            $pageDetails['id'] = 0;
            $pageDetails["articleUrl"] = $articleUrl = "404.php";
            $pageDetails["pageName"] = $pageName = "Page not found";
        } else {
            $baseUrl = './files/';
            $pageDetails = $pageDetails[0];
            $articleUrl = $pageDetails["articleUrl"];
            $pageName = $pageDetails["pageName"];
        }

        include "./files/header.php";
        include $baseUrl . $articleUrl;

        insertTemplate("./files/footer.php", [
            "calcDuration" => $_ENV["DEV_MODE"],
        ]);
    }

    public static function close()
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

    private static function get_script($script)
    {
        header('Content-Type: text/javascript');

        /* workaround for colorpicker and other packages */
        if ($script == "/colorpicker.js") {
            echo file_get_contents("node_modules/colorpicker/min/colorpicker.js");
            return;
        }

        if ($script == "/notifications.js") {
            echo file_get_contents("node_modules/js-classes/notifications.js");
            return;
        }

        /* tableconfig.js */
        if ($script == "/tableconfig.js" && $_ENV["DEV_MODE"]) {
            TableConfig::generate();
            return;
        }

        $fileName = explode(".", $script);

        /* check if filename has .js ending */
        if (!(sizeof($fileName) == 2)) {
            echo "";
            return;
        }

        $min = "min/" . $fileName[0] . ".js.gz";
        if (
            file_exists(Link::getResourcesLink($min, "js", false))
            && MINIFY_STATUS
        ) {
            header('Content-Encoding: gzip');
            echo file_get_contents(Link::getResourcesLink($min, "js", false));
            return;
        }

        if (file_exists(Link::getResourcesLink($script, "js", false))) {
            echo file_get_contents(Link::getResourcesLink($script, "js", false));
            return;
        }
        
        echo "";
    }

    private static function get_css($script)
    {
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
            if (
                file_exists(Link::getResourcesLink($min, "css", false))
                && MINIFY_STATUS
            ) {
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
        $file = file_get_contents(Link::getResourcesLink($font, "font", false));

        echo $file;
    }

    private static function get_upload($upload)
    {
        $file_info = new \finfo(FILEINFO_MIME_TYPE);

        if (!file_exists(Link::getResourcesLink($upload, "upload", false))) {
            $mime_type = $file_info->buffer("img/default_image.png");
            header("Content-type:$mime_type");
            $file = file_get_contents("img/default_image.png");

            echo $file;
            return;
        }

        $mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($upload, "upload", false)));

        header("Content-type:$mime_type");
        $file = file_get_contents(Link::getResourcesLink($upload, "upload", false));

        echo $file;
    }

    private static function get_backup($backup)
    {
        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer(file_get_contents(Link::getResourcesLink($backup, "backup", false)));

        header("Content-type:$mime_type");
        $file = file_get_contents(Link::getResourcesLink($backup, "backup", false));

        echo $file;
    }

    private static function get_pdf_invoice($pdf)
    {
        header("Content-type: application/pdf");
        $file = file_get_contents(Link::getResourcesLink($pdf, "pdf", false));

        echo $file;
    }

    private static function get_image($file)
    {
        header("Content-type: " .  mime_content_type("img/" . $file));
        $file = file_get_contents("img/" . $file);

        echo $file;
    }

    private static function get_static($file)
    {
        if ($file == "facebook-product-export") {
            header("Content-type: text/csv");
            $filename = "exportFB_" . date("Y-m-d") . ".csv";
            $file = file_get_contents(Link::getResourcesLink($filename, "csv", false));
            echo $file;
            // TODO: check if file exists and if not, return latest file
        } else if ($file == "/generate-facebook") {
            $exportFacebook = new ExportFacebook();
            $exportFacebook->generateCSV();
        } else if ($file == "/import-search-console") {
            ImportGoogleSearchConsole::import();
        }
    }

    public static function outputHeaderJSON()
    {
        session_start();

        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Content-Type: application/json; charset=utf-8');
    }
}
