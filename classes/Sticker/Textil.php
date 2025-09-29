<?php

namespace Classes\Sticker;

use Classes\Project\Produkt;
use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Textil extends Sticker
{
    public const TYPE = "textil";

    /**
     * SELECT color, prstshp_attribute_lang.name FROM `prstshp_attribute`, prstshp_attribute_lang WHERE prstshp_attribute.id_attribute = prstshp_attribute_lang.id_attribute AND id_attribute_group = 11 AND prstshp_attribute_lang.id_lang = 1;
     * TODO: read from shop and cache
     * 
     * @var array<int, array{hexCol: string, name: string}>
     */
    public array $textilColors = [
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

    private bool $isShirtcollection = false;
    private bool $isColorable = false;

    public function __construct(int $idTextile)
    {
        parent::__construct($idTextile);
        $this->instanceType = "textil";

        /* is true, if sticker exists or is activated */
        $this->isShirtcollection = $this->stickerData["is_shirtcollection"] == "1";
        $this->isColorable = $this->stickerData["is_colorable"] == "1";
    }

    public function isInShop(): bool
    {
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): string
    {
        return "Textil " . parent::getName();
    }

    public function getIsShirtcollection(): bool
    {
        return $this->isShirtcollection;
    }

    public function getIsColorable(): bool
    {
        return $this->isColorable;
    }

    public function getIsCustomizable(): bool
    {
        return $this->stickerData["is_customizable"] == "1";
    }

    public function getIsForConfigurator(): bool
    {
        return $this->stickerData["is_for_configurator"] == "1";
    }

    public function getAltTitle(string $default = ""): string
    {
        return parent::getAltTitle(self::TYPE);
    }

    public function getShopLink(): string
    {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function getPriceTextilFormatted(): string
    {
        $price = number_format($this->getPrice(), 2, ',', '') . "€";
        return $price;
    }

    public function getPrice(): int
    {
        return 0;
    }

    public function toggleIsColorable(): void
    {
        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_colorable` = NOT `is_colorable` WHERE id = :id", ["id" => $this->getId()]);
        $this->isColorable = !$this->isColorable;
    }

    /**
     *  returns the image array for the current svg
     * 
     * @return array<string, mixed>
     */
    public function getCurrentSVG(): ?array
    {
        return $this->imageData->getTextilSVG($this->isColorable);
    }

    private function uploadSVG(): void
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
    public function getBasePrice(): string
    {
        $price = (string) $this->getPrice();
        return $price;
    }

    /**
     * idCategory für Textilien ist 25
     * TODO: überarbeiten, da hardcoded
     */
    public function getIdCategory(): int
    {
        return 25;
    }

    public function save(bool $isOverwrite = false): ?string
    {
        if (!$this->getIsShirtcollection()) {
            return null;
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
        return null;
    }

    /**
     * returns hardcoded color ids
     * 
     * @return array<int, array<int>>
     */
    public function getAttributes(): array
    {
        if ($this->getIsColorable()) {
            return [
                [164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183]
            ];
        }
        return [];
    }

    public function generateAllTextiles(): void
    {
        $category = 0;
        $textiles = Produkt::getAllProducts($category);

        foreach ($textiles as $textil) {
            $idTextile = $textil->getProductId();
            $idSticker = $this->idSticker;
            //$images = StickerImage::getCombinedImages($idSticker, $idTextile);
            // TODO: add product to prestashop

            $this->save();
        }
    }

    /**
     * product category is hardcoded
     * 
     * @return array<string, array<string, mixed>>
     */
    public function getProducts(): array
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

    public static function makeColorizable(): void
    {
        $id = (int) Tools::get("id");

        $textil = new Textil($id);
        $textil->toggleIsColorable();
        $file = $textil->getCurrentSVG();

        if ($file == null) {
            JSONResponseHandler::returnNotFound(["status" => "no file found"]);
        }
            
        $url = Link::getResourcesShortLink($file["dateiname"], "upload");
        JSONResponseHandler::sendResponse(["url" => $url]);
    }

    public static function toggleTextile(): void
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

    public static function setPrice(): void
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

    private static function checkIfExists(int $idSticker, int $idProduct): bool
    {
        $query = "SELECT id FROM module_sticker_textiles WHERE id_module_textile = :idSticker AND id_product = :idProduct";
        $result = DBAccess::selectQuery($query, [
            "idSticker" => $idSticker,
            "idProduct" => $idProduct
        ]);

        return sizeof($result) > 0;
    }
}
