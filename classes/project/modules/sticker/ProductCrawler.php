<?php

/*
 * TODO: nginx is still buffering, these headers do not affect it
 * nginx buffering könnte man mit https://stackoverflow.com/questions/63293990/how-can-i-track-upload-progress-from-app-behind-nginx-reverse-proxy
 * sowas in der Art umgehen. Ich denke aber, dass ein Websocket oder eine Schleife mit mehreren Requests sinnvoller wäre.
 * So wichtig ist es aber auch nicht.
 */
require_once("classes/project/modules/sticker/SearchProducts.php");

class ProductCrawler extends PrestashopConnection {

    /**
     * alle produkte durchgehen
     * alle ids checken und wenn nicht existert, dann wird ein neuer eintrag erstellt
     * daten werden eingetragen
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * https://stackoverflow.com/questions/9152373/php-flushing-while-loop-data-with-ajax
     * Das Script soll auch laufen, falls der Nutzer die Seite neu lädt.
     */
    public function crawlAll() {
        ignore_user_abort(true);
        set_time_limit(0);

        $xml = $this->getXML("products");
        $allProducts = $xml->products->product;

        foreach ($allProducts as $product) {
            $idProduct = (int) $product["id"];
            $productXml = $this->getXML("products/$idProduct");

            $productData = $productXml->product;
            $idMotiv = (int) $productData->reference;

            if ($idMotiv != null || $idMotiv != 0) {
                $checkIfExists = DBAccess::selectQuery("SELECT id FROM `module_sticker_sticker_data` WHERE id = :idMotiv LIMIT 1", ["idMotiv" => $idMotiv]);

                /* if product exists, update product info, otherwise create it */
                if ($checkIfExists != null) {
                    $category = $this->getCategory($productData);
                    $this->updateCategory($idMotiv, $category);

                    $this->getImages($productData, $category);
                } else {
                    $this->analyseProduct($productData);
                }
            }
        }
    }

    private function analyseProduct($productData) {
        $idMotiv = (int) $productData->reference;
        $title = (String) $productData->name->language[0];
        $category = $this->getCategory($productData);

        /* if category is 0, the product does not belong to the sticker upload program */
        if ($category == 0) {
            return;
        }

        $this->getImages($productData, $category);

        $creationDate = $productData->date_add;
        $creationDate = date("Y-m-d", strtotime($creationDate));

        $query = "REPLACE INTO `module_sticker_sticker_data` (`id`, `name`, `creation_date`) VALUES (:idMotiv, :title, :creationDate);";

        DBAccess::updateQuery($query, ["idMotiv" => $idMotiv, "title" => $title, "creationDate" => $creationDate]);
        $this->updateCategory($idMotiv, $category);
    }

    /**
     * checkt, ob der Artikel in einer der drei getrackte Produktkategorien ist
     * 25: Textil
     * 62: Wandtattoo
     * 13: Aufkleber
     */
    private function getCategory($productData) {
        $categories = $productData->associations->categories;
        $idCategories = [];

        foreach ($categories->category as $category) {
            $id = (int) $category->id;
            array_push($idCategories, $id);
        }

        if (in_array(25, $idCategories)) {
            return 25;
        } else if (in_array(62, $idCategories)) {
            return 62;
        } else if (in_array(13, $idCategories)) {
            return 13;
        }

        return 0;
    }

    /**
     * updates the category info of the product and sets additonal_data column
     */
    private function updateCategory($id, $category) {
        switch ($category) {
            case 25:
                $query = "UPDATE `module_sticker_sticker_data` SET is_shirtcollection = 1 WHERE id = :id;";
                break;
            case 62:
                $query = "UPDATE `module_sticker_sticker_data` SET is_walldecal = 1 WHERE id = :id;";
                break;
            case 13:
                $query = "UPDATE `module_sticker_sticker_data` SET is_plotted = 1 WHERE id = :id;";
                break;
            default:
                $query = "";
        }

        if ($query == "")
            return null;
        DBAccess::updateQuery($query, ["id" => $id]);

        /* sets the additional_data column */
        $matches = SearchProducts::getProductsByStickerId($id);
        $matchesJson = json_encode($matches, JSON_UNESCAPED_UNICODE);
        DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET additional_data = '$matchesJson' WHERE id = :id;", ["id" => $id]);
    }

    /**
     * vorgehen:
     * img daten holen und mit den daten am server vergleichen
     * dann bilder, die fehlen, herunterladen
     */

    private function getImages($productData, $category) {
        $idMotiv = (int) $productData->reference;
        $idProduct = (int) $productData->id;

        $queryImageStatus = "SELECT * FROM module_sticker_image WHERE id_motiv = :idMotiv";
        $dataImageStatus = DBAccess::selectQuery($queryImageStatus, ["idMotiv" => $idMotiv]);

        $idImagesDownloaded = [];

        /* 
         * compares all already stored images to the shop images,
         * if the image is already downloaded,
         * then do nothing,
         * if not, download the image and store the necessary data
         */
        foreach ($dataImageStatus as $imageComp) {
            $idImageShop = (int) $imageComp["id_image_shop"];

            /* if is 0, the image was not yet uploaded to the shop */
            if ($idImageShop != 0) {
                $idImagesDownloaded[] = $idImageShop;
            }
        }

        $images = $productData->associations->images;
        $imagesData = [];

        /* collect image data */
        foreach ($images->image as $image) {
            $idImage = (int) $image->id;

            $imagesData[] = $idImage;

            if (!in_array($idImage, $idImagesDownloaded)) {
                $filename = $this->downloadImage($idProduct, $idImage, $idMotiv);
                $this->saveDownloadedImageToDB($filename, $idMotiv, $category, $idProduct, $idImage);
            }
        }
    }

    private function downloadImage($idProduct, $idImage, $idMotiv) {
        $ch = curl_init(SHOPURL . "api/images/products/$idProduct/$idImage");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, SHOPKEY . ':');
        $image = curl_exec($ch);
        curl_close($ch);

        $filename = $idProduct . "_" . $idMotiv . "_" . $idImage . ".jpg";
        $fp = fopen("upload/$filename", 'w');
        fwrite($fp, $image);
        fclose($fp);

        return $filename;
    }

    private function saveDownloadedImageToDB($filename, $idMotiv, $category, $idProduct, $idImageShop) {
        /* write image info to db */
        $query = "INSERT INTO dateien (dateiname, originalname, `date`, `typ`) VALUES ('$filename', '$filename', '$today', 'jpg')";
        $id_datei = DBAccess::insertQuery($query);
        /* TODO: über StickerImage machen */
        $query = "INSERT INTO dateien_motive (id_datei, id_motive) VALUES ($id_datei, $idMotiv);";
        DBAccess::insertQuery($query);

        switch ($category) {
            case 25:
                $key = "is_textil";
                break;
            case 13:
                $key = "is_aufkleber";
                break;
            case 62;
                $key = "is_wandtattoo";
                break;
            default:
                $key = "";
                break;
        }

        /* TODO: über StickerImage machen */
        if ($key != "") {
            $query = "INSERT INTO module_sticker_images (id_image, id_sticker, $key) VALUES ($id_datei, $idMotiv, 1);";
            DBAccess::insertQuery($query);
        }
    }

}

?>
