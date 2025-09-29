<?php

namespace Classes\Sticker;

use Classes\Link;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use MaxBrennemann\PhpUtilities\DBAccess;

class StickerImage extends PrestashopConnection
{
    public const IMAGE_AUFKLEBER = "aufkleber";
    public const IMAGE_WANDTATTOO = "wandtattoo";
    public const IMAGE_TEXTIL = "textil";
    public const IMAGE_TEXTILSVG = "textilsvg";

    private int $idMotiv;

    /** @var array<int, mixed> */
    private array $allFiles = [];
    /** @var array<int, mixed> */
    private array $images = [];
    /** @var array<int, mixed> */
    private array $files = [];

    private string $currentType = "";

    /** @var array<int, mixed> */
    private array $svgs = [];

    public function __construct(int $idMotiv)
    {
        parent::__construct();

        $this->idMotiv = $idMotiv;
        $this->getConnectedFiles();
        $this->prepareImageData();
    }

    /**
     * @return array<int, mixed>
     */
    public static function getAllImageFiles(): array
    {
        $query = "SELECT dateien.dateiname, dateien.originalname AS alt, 
                dateien.typ, dateien.id, module_sticker_image.image_sort, module_sticker_image.id_product, module_sticker_image.description, module_sticker_image.id_image_shop, module_sticker_image.image_order
            FROM dateien, module_sticker_image 
            WHERE dateien.id = module_sticker_image.id_datei
                AND (
                    module_sticker_image.image_sort = 'aufkleber' OR
                    module_sticker_image.image_sort = 'wandtattoo' OR
                    module_sticker_image.image_sort = 'textil'
                );";
        return DBAccess::selectQuery($query);
    }

    private function getConnectedFiles(): void
    {
        $allFiles = DBAccess::selectQuery(
            "SELECT dateien.dateiname, dateien.originalname AS alt, 
                dateien.typ, dateien.id, module_sticker_image.image_sort, module_sticker_image.id_product, module_sticker_image.description, module_sticker_image.id_image_shop, module_sticker_image.image_order
            FROM dateien, module_sticker_image 
            WHERE dateien.id = module_sticker_image.id_datei
                AND module_sticker_image.id_motiv = :idMotiv;",
            ["idMotiv" => $this->idMotiv]
        );

        $this->allFiles = $allFiles;
        foreach ($this->allFiles as $f) {
            /* https://stackoverflow.com/questions/15408125/php-check-if-file-is-an-image */
            $fileName = "upload/" . $f["dateiname"];
            if (!file_exists($fileName)) {
                continue;
            }

            if (@is_array(getimagesize($fileName))) {
                $this->images[] = $f;
            } else {
                $this->files[] = $f;
            }
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getSVGIfExists(bool $colorable = false): string
    {
        $f = $this->getTextilSVG($colorable);
        if ($f == null) {
            return "";
        }
        return Link::getResourcesShortLink($f["dateiname"], "upload");
    }

    /**
     * adds new attributes "link" and "title" to all images 
     */
    private function prepareImageData(): void
    {
        foreach ($this->images as &$image) {
            $image["link"] = Link::getResourcesShortLink($image["dateiname"], "upload");
            $image["title"] = "Produktbild";
        }
    }

    /**
     * @return list<array<string, string>>
     */
    public function getAufkleberImages(): array
    {
        return $this->getImagesByType(self::IMAGE_AUFKLEBER);
    }

    /**
     * @return list<array<string, string>>
     */
    public function getWandtattooImages(): array
    {
        return $this->getImagesByType(self::IMAGE_WANDTATTOO);
    }

    /**
     * @return list<array<string, string>>
     */
    public function getTextilImages(): array
    {
        return $this->getImagesByType(self::IMAGE_TEXTIL);
    }

    /**
     * @param string $type
     * @return list<array<string, string>>
     */
    private function getImagesByType(string $type): array
    {
        $images = array_filter(
            $this->images,
            fn($element) => $element["image_sort"] == $type
        );

        // Sort the array by the "image_order" attribute
        usort($images, function ($a, $b) {
            return $a["image_order"] - $b["image_order"];
        });

        return $images;
    }

    /**
     * @param bool $colorable
     * @return array<string, string>
     */
    public function getTextilSVG(bool $colorable = false): ?array
    {
        foreach ($this->files as $f) {
            if ($f["image_sort"] == "textilsvg") {
                if ($colorable) {
                    return $this->makeSVGColorable($f);
                }
                return $f;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $file
     * @return void
     */
    public function resizeImage(array $file): void
    {
        list($width, $height) = getimagesize("upload/" . $file["dateiname"]);
        /* width and height do not matter any longer, images are only resized if filesize exeeds 2MB */
        if (filesize("upload/" . $file["dateiname"]) >= 2000000) {
            switch ($file["typ"]) {
                case "jpg":
                    if (function_exists("imagecreatefromjpeg")) {
                        $image = imagecreatefromjpeg("upload/" . $file["dateiname"]);
                        $imgResized = imagescale($image, 700, 700 * ($height / $width));
                        imagejpeg($imgResized, "upload/" . $file["dateiname"]);
                    }
                    break;
                case "png":
                    if (function_exists("imagecreatefrompng")) {
                        $image = imagecreatefrompng("upload/" . $file["dateiname"]);
                        $imgResized = imagescale($image, 700, 700 * ($height / $width));
                        imagepng($imgResized, "upload/" . $file["dateiname"]);
                    }
                    break;
                default:
                    return;
            }
        }
    }

    public function getFirstImageLink(): void {}

    public function convertJPGtoAvif(): void {}

    private function saveImage(string $filename): void
    {
        /* add file to db */
        $query = "INSERT INTO `dateien` (`dateiname`, `originalname`, `date`, `typ`) VALUES (:newFile1, :newFile2, :today, 'svg');";
        $fileId = DBAccess::insertQuery($query, [
            "newFile1" => $filename,
            "newFile2" => $filename,
            "today" => date("Y-m-d")
        ]);

        $query = "INSERT INTO module_sticker_image (id_datei, id_motiv, image_sort) VALUES (:id, :motivnummer, :imageCategory)";
        $params = [
            "id" => $fileId,
            "motivnummer" => $this->idMotiv,
            "imageCategory" => "textilsvg",
        ];
        DBAccess::insertQuery($query, $params);
    }

    /* SVG section */
    public function getSVGCount(): int
    {
        return sizeof($this->svgs);
    }

    public function getSVG(int $number = 0): string
    {
        $svgs = [];
        foreach ($this->files as $f) {
            if ($f["typ"] == "svg") {
                $svgs[] = $f;
            }
        }

        $this->svgs = $svgs;
        if (sizeof($svgs) > $number) {
            return "upload/" . $svgs[$number]["dateiname"];
        }

        return "";
    }

    /**
     * seaches for all occurances of colors in these two patterns:
     * fill:#FFFFFF
     * fill:#FFF
     * then it replaces "<svg" with "<svg id="svg_elem" only if it is not already set
     * 
     * @param array<string, mixed> $f
     * @return array<string, mixed>
     */
    public function makeSVGColorable(array $f): ?array
    {
        $filename = $f["dateiname"];
        if ($filename == "") {
            return null;
        }

        $newFile = substr($filename, 0, -4);
        $newFile .= "_colorable.svg";

        if (!file_exists("upload/" . $newFile)) {
            if (!file_exists($filename)) {
                return null;
            }

            $file = file_get_contents($filename);

            if ($file == false) {
                return null;
            }

            /* remove all fills */
            $file = preg_replace('/fill:#([0-9a-f]{6}|[0-9a-f]{3})/i', "", $file);

            /* remove all strokes */
            $file = preg_replace('/stroke:#([0-9a-f]{6}|[0-9a-f]{3})/i', "", $file);

            if (!str_contains($file, "<svg id=\"svg_elem\"")) {
                $file = str_replace("<svg", "<svg id=\"svg_elem\"", $file);
            }

            file_put_contents("upload/" . $newFile, $file);

            $this->saveImage($newFile);
            $f["dateiname"] = $newFile;
            return $f;
        } else {
            $f["dateiname"] = $newFile;
            return $f;
        }
    }

    public function uploadSVG(int $number): void {}

    public static function handleSVGStatus(int $idMotiv): void
    {
        $query = "DELETE FROM module_sticker_image WHERE id_motiv = :idMotiv AND image_sort = 'textilsvg';";
        DBAccess::deleteQuery($query, ["idMotiv" => $idMotiv]);
    }

    /**
     * uploads all images to the shop using the json responder script on the server;
     *
     * @param list<array<string, string>> $imageURLs array of image urls
     * @param int $productId id of the product in the shop
     *
     * @return void
     */
    public function uploadImages(array $imageURLs, int $productId): void
    {
        if ($imageURLs == null) {
            return;
        }

        $imageURLS = $this->stripUnsupportedFileTypes($imageURLs);

        if ($_ENV["DEV_MODE"] == true) {
            $result = $this->directUpload($imageURLs, $productId);
            $this->processImageIds($result, $imageURLs);
        } else {
            $result = $this->generateImageUrls($imageURLs, $productId);
            $this->processImageIds($result, $imageURLs);
        }
    }

    /**
     * removes all images that are not supported by the shop
     * TODO: check array vs list
     *
     * @param list<array<string, string>> $images
     * @return list<array<string, string>>
     */
    private function stripUnsupportedFileTypes(array $images): array
    {
        $unsupported = ["svg", "eps", "ai", "webp", "avif"];
        foreach ($images as $key => $image) {
            if (in_array($image["typ"], $unsupported)) {
                unset($images[$key]);
            }
        }

        /* reindex array */
        $images = array_values($images);
        return $images;
    }

    /**
     * generates the image urls and sends them to the shop using the json responder script on the server;
     *
     * @param array<int, mixed> $imageURLs array of image urls
     * @param int $productId id of the product in the shop
     *
     * @return string
     */
    private function generateImageUrls(array $imageURLs, int $productId): string
    {
        /* https://www.prestashop.com/forums/topic/407476-how-to-add-image-during-programmatic-product-import/ */
        $images = array();
        $first = true;
        foreach ($imageURLs as $i) {
            $link = $_ENV["WEB_URL"] . "upload/" . $i["dateiname"];
            $images[] = [
                "url" => urlencode($link),
                "cover" => $first,
            ];

            $first = false;
        }

        /* json resonder script on server */
        $ch = curl_init($this->url);

        $payload = ["images" => $images, "id" => $productId];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result == false) {
            return "";
        }

        return $result;
    }

    /**
     * this is currently a workaround for them problem that prestashop wants urls for image upload;
     * I upload the images to the server that generates urls which are then passed to prestashop
     *
     * @param array<int, mixed> $imageURLs array of image urls
     * @param $productId id of the product in the shop
     *
     * @return string
     */
    private function directUpload(array $imageURLs, int $productId): string
    {
        $client = new Client();
        $result = "";
        $files = [];

        foreach ($imageURLs as $i) {
            $path = "upload/" . $i["dateiname"];
            $files[] = [
                'name' => 'image[]',
                'contents' => fopen($path, 'r'),
            ];
        }

        $files[] = [
            'name' => 'uploadImage',
            'contents' => true,
        ];

        $files[] = [
            'name' => 'id',
            'contents' => $productId,
        ];

        try {
            $response = $client->post($this->url, [
                'multipart' => $files,
            ]);

            $result = $response->getBody()->getContents();
        } catch (RequestException $e) {
        }

        return $result;
    }

    /**
     * uploads the image descriptions to the shop using the json responder script on the server;
     * @param array<int, array<string, string>> $descriptions
     */
    public function uploadImageDescription(array $descriptions): void
    {
        $client = new Client();
        $client->request('POST', $_ENV["SHOPURL"] . "/auftragsbearbeitung/setImageDescription.php", [
            'form_params' => [
                'descriptions' => json_encode($descriptions),
            ],
        ]);
    }

    /**
     * sets the image ids in the database after the images were uploaded to the shop
     * 
     * @param array<int, mixed> $imageURLs
     */
    private function processImageIds(string $result, array $imageURLs): void
    {
        $imagesData = json_decode($result, true);
        $index = 0;
        foreach ($imagesData as $image) {
            $idImage = (int) $image["id"];
            $idDatei = $imageURLs[$index]["id"];
            DBAccess::updateQuery("UPDATE module_sticker_image SET id_image_shop = :idImage WHERE id_datei = :idDatei;", [
                "idImage" => $idImage,
                "idDatei" => $idDatei,
            ]);
            $index++;
        }
    }

    /**
     * deletes an image from the shop
     */
    public function deleteImage(int $idProduct, int $idImageShop): string
    {
        return $this->deleteXML("images/products/$idProduct", $idImageShop);
    }

    /**
     * deletes all images in the shop that are connected to the current product
     *
     * @param $idProduct id of the product in the shop
     * @return array<int, string>
     */
    public function deleteAllImages(int $idProduct): ?array
    {
        try {
            $xml = $this->getXML("images/products/$idProduct");

            if ($xml == null) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        $msgs = [];
        foreach ($xml->children()->children() as $image) {
            $id = (int) $image->attributes()["id"];
            $msgs[] = $this->deleteImage($idProduct, $id);
        }

        return $msgs;
    }

    /**
     * syncs the images of a product with the images in the database and deletes
     * all images that are not in the database,
     * also sets the image description
     *
     * @param $type type of the product, e.g. "aufkleber"
     * @param $productId id of the product in the shop
     *
     * @return void
     */
    public function handleImageProductSync(string $type, int $productId): void
    {
        $this->currentType = $type;

        $images = $this->getImagesByType($type);
        $status = $this->checkImageStatus($images, $productId);
        $this->uploadImages($status["missingImages"], $productId);

        $imageDescriptions = [];
        foreach ($images as $i) {
            $imageDescriptions[] = [
                "id" => $i["id_image_shop"],
                "description" => $i["description"],
            ];
        }

        $this->uploadImageDescription($imageDescriptions);

        /* delete all images that are not in the database */
        foreach ($status["deleteImages"] as $i) {
            $this->deleteImage($productId, $i);
        }

        $this->manageImageOrder();

        $this->currentType = "";
    }

    /**
     * checks if all images are on the server and if they are in the correct order,
     * returns an array with the missing images and the images that are not in the database
     *
     * @param list<array<string, string>> $images array of images from the database
     * @param int $productId id of the product in the shop
     *
     * @return array<string, mixed> with missing images and images that are not in the database
     */
    private function checkImageStatus(array $images, int $productId): array
    {
        try {
            $xml = $this->getXML("images/products/$productId");
        } catch (\Exception $e) {
            echo $e->getMessage();
            return [
                "deleteImages" => [],
                "missingImages" => $images,
            ];
        }

        $imageIds = [];

        foreach ($xml->children()->children() as $image) {
            $imageIds[] = (int) $image->attributes()["id"];
        }

        $imageIds = array_unique($imageIds);

        if (count($imageIds) == 0) {
            return [
                "deleteImages" => [],
                "missingImages" => $images,
            ];
        }

        return $this->compareIds($imageIds, $images);
    }

    /**
     * compares the ids of the images in the database with the ids of the images in the shop
     *
     * @param array<int, int> $inShop array of image ids in the shop
     * @param list<array<string, string>> $inDatabase array of image ids in the database
     *
     * @return array<string, mixed> with missing images and images that are not in the database
     */
    private function compareIds(array $inShop, array $inDatabase): array
    {
        $delete = [];
        $upload = [];

        foreach ($inDatabase as $i) {
            if (!in_array($i["id_image_shop"], $inShop)) {
                $upload[] = $i;
            }
        }

        foreach ($inShop as $i) {
            if (!$this->inArrayDB($i, $inDatabase)) {
                $delete[] = $i;
            }
        }

        return [
            "deleteImages" => $delete,
            "missingImages" => $upload,
        ];
    }

    /**
     * checks if an image is in the database
     *
     * @param $id id of the image
     * @param list<array<string, string>> $inDB array of images from the database
     *
     * @return bool true if the image is in the database, false otherwise
     */
    private function inArrayDB(int $id, array $inDB): bool
    {
        foreach ($inDB as $i) {
            if ($i["id_image_shop"] == $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * sets the image order in the shop according to the order in the database
     */
    private function manageImageOrder(): void
    {
        $query = "SELECT id_image_shop, image_order 
            FROM module_sticker_image 
            WHERE id_motiv = :idMotiv 
                AND image_sort = :imageSort
            ORDER BY image_order DESC, id_datei ASC;";
        $images = DBAccess::selectQuery($query, [
            "idMotiv" => $this->idMotiv,
            "imageSort" => $this->currentType,
        ]);

        $imageIds = [];
        foreach ($images as $i) {
            if ($i["image_order"] == null) {
                continue;
            }
            $imageIds[] = $i["id_image_shop"];
        }

        $client = new Client();
        try {
            $client->post($this->url, [
                'json' => [
                    'positions' => $imageIds,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);
        } catch (\Exception $e) {
            echo 'Request error: ' . $e->getMessage();
        }
    }

    /**
     * orders the images according to the json string in the local database
     */
    public static function setImageOrder(string $order): void
    {
        $order = json_decode($order);
        $count = 0;
        foreach ($order as $id) {
            $query = "UPDATE module_sticker_image SET image_order = :order WHERE id_datei = :id;";
            DBAccess::updateQuery($query, [
                "order" => $count,
                "id" => $id
            ]);
            $count++;
        }
    }

    public static function getCombinedImages(int $stickerId, int $textileId): void {}

    /**
     * @param array<string, mixed> $data
     */
    public static function prepareData(array $data): void
    {
        foreach ($data["results"] as $key => $value) {
            $dateiname = $data["results"][$key]["dateiname"];
            $name = $data["results"][$key]["originalname"];
            $image = Link::getResourcesShortLink($dateiname, "upload");

            $html = '<img src="' . $image . '" title="' . $name . '" class="w-24">';
            $data["results"][$key]["image"] = $html;
        }
    }
}
