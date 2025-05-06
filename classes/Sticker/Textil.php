<?php

namespace Classes\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Project\Produkt;

class Textil extends Sticker
{

    const TYPE = "textil";

    /*
     * SELECT color, prstshp_attribute_lang.name FROM `prstshp_attribute`, prstshp_attribute_lang WHERE prstshp_attribute.id_attribute = prstshp_attribute_lang.id_attribute AND id_attribute_group = 11 AND prstshp_attribute_lang.id_lang = 1; 
     * TODO: read from shop and cache
     */
    public $textilColors = [
        ["hexCol" => "#ffffff", "name" => "Weiss"],
        ["hexCol" => "#FCCC00", "name" => "Maisgelb"],
        ["hexCol" => "#F5E61A", "name" => "Gelb"],
        ["hexCol" => "#910C19", "name" => "Rot"],
        ["hexCol" => "#DB3400", "name" => "Orange"],
        ["hexCol" => "#000000", "name" => "Schwarz"],
        ["hexCol" => "#11307D", "name" => "Königsblau"],
        ["hexCol" => "#0053AA", "name" => "Enzianblau"],
        ["hexCol" => "#009999", "name" => "Helltuerkis"],
        ["hexCol" => "#004429", "name" => "Dunkelgruen"],
        ["hexCol" => "#008955", "name" => "Hellgruen"],
        ["hexCol" => "#60C340", "name" => "Apfelgruen"],
        ["hexCol" => "#45291E", "name" => "Braun"],
        ["hexCol" => "#2C2E31", "name" => "Anthrazit"],
        ["hexCol" => "#748289", "name" => "Silber"],
        ["hexCol" => "#878A8D", "name" => "Grau"],
        ["hexCol" => "#ccff00", "name" => "Neongelb"],
        ["hexCol" => "#00ff00", "name" => "Neongruen"],
        ["hexCol" => "#fd5f00", "name" => "Neonorange"],
        ["hexCol" => "#ff019a", "name" => "Neonpink"],
    ];

    private $isShirtcollection = false;
    private $isColorable = false;

    function __construct($idTextile)
    {
        parent::__construct($idTextile);
        $this->instanceType = "textil";

        /* is true, if sticker exists or is activated */
        $this->isShirtcollection = (int) $this->stickerData["is_shirtcollection"];
        $this->isColorable = (int) $this->stickerData["is_colorable"];
    }

    public function isInShop()
    {
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): String
    {
        return "Textil " . parent::getName();
    }

    public function getIsShirtcollection()
    {
        return $this->isShirtcollection;
    }

    public function getIsColorable()
    {
        return $this->isColorable;
    }

    public function getIsCustomizable()
    {
        return $this->stickerData["is_customizable"];
    }

    public function getIsForConfigurator()
    {
        return $this->stickerData["is_for_configurator"];
    }

    public function getAltTitle($default = ""): String
    {
        return parent::getAltTitle(self::TYPE);
    }

    public function getShopLink()
    {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function getPriceTextilFormatted()
    {
        $price = number_format($this->getPrice(), 2, ',', '') . "€";
        return $price;
    }

    public function getPrice()
    {
        return 0;
    }

    public function toggleIsColorable()
    {
        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_colorable` = NOT `is_colorable` WHERE id = :id", ["id" => $this->getId()]);
        $this->isColorable = !$this->isColorable;
    }

    public function toggleCustomizable()
    {
        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_customizable` = NOT `is_customizable` WHERE id = :id", ["id" => $this->getId()]);
    }

    public function toggleConfig()
    {
        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_for_configurator` = NOT `is_for_configurator` WHERE id = :id", ["id" => $this->getId()]);
    }

    /* returns the image array for the current svg */
    public function getCurrentSVG()
    {
        return $this->imageData->getTextilSVG($this->isColorable);
    }

    private function uploadSVG()
    {
        $url = $this->url . "?upload=svg&id=" . $this->idProduct;
        $currentSVG = $this->getCurrentSVG();
        $filename = "upload/" . $currentSVG["dateiname"];
        $cImage = new \CurlFile($filename, 'image/svg+xml', "image");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cImage));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        echo $result;
    }

    /**
     * aktuell ist der basePrice bei Textilien gleich dem Endpreis, muss später noch geändert werden
     * TODO: Textilpreise dynamisch gestalten
     */
    public function getBasePrice()
    {
        return $this->getPrice();
    }

    /**
     * idCategory für Textilien ist 25
     * TODO: überarbeiten, da hardcoded
     */
    public function getIdCategory()
    {
        return 25;
    }

    public function save($isOverwrite = false)
    {
        if (!$this->getIsShirtcollection()) {
            return;
        }

        $productId = (int) $this->getIdProduct();
        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescription(), $this->getDescriptionShort());
        $stickerUpload->setIdCategoryPrimary($this->getIdCategory());

        $stickerCombination = new StickerCombination($this);

        if ($productId == 0) {
            $this->idProduct = $productId = $stickerUpload->createSticker();
        } else {
            $stickerUpload->updateSticker($productId);
        }

        $stickerUpload->setCategoires([$this->getIdCategory()]);
        $stickerCombination->removeOldCombinations($productId);

        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($productId);
        $stickerTagManager->saveTags();

        $stickerCombination->createCombinations();

        $this->connectAccessoires();

        $this->uploadSVG();

        if ($isOverwrite) {
            $this->imageData->deleteAllImages($this->idProduct);
        }

        $this->imageData->handleImageProductSync("textil", $this->idProduct);
    }

    /**
     * returns hardcoded color ids
     */
    public function getAttributes()
    {
        if ($this->getIsColorable()) {
            return [
                [164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183]
            ];
        }
        return [];
    }

    public function generateAllTextiles()
    {
        $category = 0;
        $textiles = Produkt::getAllProducts($category);

        foreach ($textiles as $textil) {
            $idTextile = $textil["id"];
            $idSticker = $this->idSticker;
            //$images = StickerImage::getCombinedImages($idSticker, $idTextile);
            // TODO: add product to prestashop

            $this->save();
        }
    }

    /**
     * product category is hardcoded
     */
    public function getProducts()
    {
        $products = Produkt::getAllProducts(2);
        $additionalData = "SELECT id, id_product, activated, price FROM module_sticker_textiles WHERE id_module_textile = :id";
        $additionalData = DBAccess::selectQuery($additionalData, [
            "id" => $this->idSticker
        ]);

        $adapter = [];
        foreach ($products as $key => $product) {
            $idProduct = $product->getProductId();
            $adapter[$key] = [
                "id" => $idProduct,
                "name" => $product->getBezeichnung(),
                "activated" => false,
                "price" => 0
            ];

            foreach ($additionalData as $data) {
                if ($data["id_product"] == $idProduct) {
                    $adapter[$key]["activated"] = $data["activated"];
                    $adapter[$key]["price"] = $data["price"];
                    break;
                }
            }
        }

        return $adapter;
    }

    public static function toggleTextile()
    {
        $idSticker = Tools::get("id");
        $idProduct = Tools::get("idTextile");
        $status = Tools::get("status");
        $status = $status == "true" ? 1 : 0;

        if (!self::checkIfExists($idSticker, $idProduct)) {
            $query = "INSERT INTO module_sticker_textiles (id_module_textile, id_product, activated) VALUES (:idSticker, :idProduct, :status)";
            DBAccess::insertQuery($query, [
                "idSticker" => $idSticker,
                "idProduct" => $idProduct,
                "status" => $status
            ]);
        } else {
            $query = "UPDATE module_sticker_textiles SET activated = :status WHERE id_module_textile = :idSticker AND id_product = :idProduct";
            DBAccess::updateQuery($query, [
                "idSticker" => $idSticker,
                "idProduct" => $idProduct,
                "status" => $status
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success"
        ]);
    }

    public static function setPrice()
    {
        $idSticker = Tools::get("id");
        $idProduct = Tools::get("idTextile");
        $price = Tools::get("price");

        if (!self::checkIfExists($idSticker, $idProduct)) {
            $query = "INSERT INTO module_sticker_textiles (id_module_textile, id_product, price) VALUES (:idSticker, :idProduct, :price)";
            DBAccess::insertQuery($query, [
                "idSticker" => $idSticker,
                "idProduct" => $idProduct,
                "price" => $price
            ]);
        } else {
            $query = "UPDATE module_sticker_textiles SET price = :price WHERE id_module_textile = :idSticker AND id_product = :idProduct";
            DBAccess::updateQuery($query, [
                "idSticker" => $idSticker,
                "idProduct" => $idProduct,
                "price" => $price
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success"
        ]);
    }

    private static function checkIfExists($idSticker, $idProduct)
    {
        $query = "SELECT id FROM module_sticker_textiles WHERE id_module_textile = :idSticker AND id_product = :idProduct";
        $result = DBAccess::selectQuery($query, [
            "idSticker" => $idSticker,
            "idProduct" => $idProduct
        ]);

        return sizeof($result) > 0;
    }
}
