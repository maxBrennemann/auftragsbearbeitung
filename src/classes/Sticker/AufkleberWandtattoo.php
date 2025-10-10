<?php

namespace Src\Classes\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class AufkleberWandtattoo extends Sticker
{
    protected float $basePrice;

    /** @var array<int, int> */
    protected array $idShopAttributes = [];
    /** @var array<int, string> */
    protected array $prices = [];
    /** @var array<int, float> */
    protected array $buyingPrices = [];
    /** @var array<int, string> */
    protected array $widthsToSizeIds = [];

    public function getPrice(int $width, int $height, int $difficulty): int
    {
        if ($width >= 1200) {
            $base = 2100;
        } elseif ($width >= 900) {
            $base = 1950;
        } elseif ($width >= 600) {
            $base = 1700;
        } elseif ($width >= 300) {
            $base = 1500;
        } else {
            $base = 1200;
        }

        $base = $base + 200 * $difficulty;
        if ($height >= 0.5 * $width) {
            $base += 100;
        }

        return $base;
    }

    public function getSize(int $sizeId): string
    {
        return $this->widthsToSizeIds[$sizeId];
    }

    /** 
     * @return array<int, string> 
     */
    public function getPricesMatched(): array
    {
        return $this->prices;
    }

    public function updatePrice(int $width, int $height, int $price): void
    {
        $currentPrice = $this->getPrice($width, $height, $this->getDifficulty());
        if ($currentPrice != $price) {
            $query = "UPDATE module_sticker_sizes SET price = :price WHERE id_sticker = :idSticker AND width = :width";
            $params =  [
                "price" => $price,
                "idSticker" => $this->getId(),
                "width" => $width,
            ];
            StickerChangelog::log($this->getId(), 0, 0, "module_sticker_sizes", "price", $price);
            DBAccess::updateQuery($query, $params);
        }
    }

    /**
     * sets new height for a sticker,
     * adjusts price if necessary,
     * writes to changelog
     * @param array<string, mixed> $data
     */
    public function updateSizeTable(array $data): void
    {
        $width = (int) $data["width"];
        $height = (int) $data["height"];
        $price = (int) $data["price"];

        $query = "UPDATE module_sticker_sizes SET height = :height, price = :price WHERE id_sticker = :id AND width = :width";
        DBAccess::updateQuery($query, [
            "height" => $height,
            "width" => $width,
            "price" => $price,
            "id" => $this->getId(),
        ]);
    }

    public function getDifficulty(): int
    {
        return $this->stickerData["price_class"];
    }

    protected function getDescriptionShortWithDefaultText(): string
    {
        return $this->getSizeTableFormatted() . $this->getDescriptionShort();
    }

    public function getSizeTableFormatted(): string
    {
        $query = "SELECT width, height
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        foreach ($data as &$d) {
            $repWidth = (string) (((int) $d["width"]) / 10);
            $repHeight = (string) (((int) $d["height"]) / 10);
            $d["width"] = str_replace(".", ",", $repWidth) . "cm";
            $d["height"] = str_replace(".", ",", $repHeight) . "cm";
        }

        return \Src\Classes\Controller\TemplateController::getTemplate("sticker/sizeTable", [
            "sizes" => $data,
        ]);
    }

    public function getBasePrice(): string
    {
        $basePrice = $this->getBasePriceUnformatted();
        //$this->basePrice = $basePrice;
        return number_format((float) $basePrice, 2, '.', '');
    }

    public function getBasePriceUnformatted(): float
    {
        if ($this->basePrice != null) {
            return $this->basePrice / 100;
        }

        $query = "SELECT price FROM module_sticker_sizes WHERE id_sticker = :idSticker ORDER BY price ASC LIMIT 1;";
        $params = ["idSticker" => $this->idSticker];
        $result = DBAccess::selectQuery($query, $params);

        if ($result == null) {
            $result[] = ["price" => "1000"];
        }

        return (float) $result[0]["price"] / 100;
    }

    /**
     * gibt die ids der id_attribute_group 5 (Breite) zurück,
     * dabei wird geprüft, ob zu dem Breitenwert schon eine id_attribute existiert und falls nicht,
     * wird diese erstellt
     * 
     * @return array<int, int>
     */
    public function getSizeIds(): array
    {
        $query = "SELECT `price`, `width`, ((`width` / 1000) * (`height` / 1000) * 10) as `costs` FROM `module_sticker_sizes` WHERE `id_sticker` = :idSticker ORDER BY `width`";
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        /* TODO: hardcoded idAttributeGroup entfernen */
        $idAttributeGroup = 5;
        $sizeIds = [];
        $widths = [];
        $prices = [];
        $buyingPrices = [];

        foreach ($data as &$d) {
            $rep = (string) (((int) $d["width"]) / 10);
            $singleSizeInCm = str_replace(".", ",", $rep) . "cm";
            $sizeId = (int) $this->addAttribute($idAttributeGroup, $singleSizeInCm);
            $sizeIds[] = $sizeId;
            $widths[$sizeId] = $singleSizeInCm;

            $price = (float) $d["price"];
            $price = $price / 100;

            $prices[$sizeId] = number_format(($price - $this->getBasePriceUnformatted()) / 1.19, 2);
            $buyingPrices[$sizeId] = (float) $d["costs"];
        }

        $this->idShopAttributes = $sizeIds;
        $this->prices = $prices;
        $this->buyingPrices = $buyingPrices;
        $this->widthsToSizeIds = $widths;
        return $sizeIds;
    }

    protected function addAttribute(int $attributeGroupId, string $attributeName): int
    {
        try {
            $xml = $this->getXML('product_option_values?filter[id_attribute_group]=' . $attributeGroupId . '&filter[name]=' . $attributeName . '&limit=1');
            $resources = $xml->children()->children();

            if (!empty($resources)) {
                $attributes = $resources->product_option_value->attributes();
                return (int) $attributes['id'];
            }
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }

        try {
            $xml = $this->getXML('product_options?schema=synopsis');
            $resources = $xml->children()->children();

            unset($resources->id);
            $resources->{"name"} = $attributeName;
            $resources->{"id_lang"} = "de";

            $opt = array(
                'resource' => 'product_options',
                'postXml' => $xml->asXML()
            );

            $this->addXML($opt);
            $id = (int) $this->xml->product->id;
            return $id;
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }

        return 0;
    }

    /**
     * @return array<int, mixed>
     */
    protected function generateDefaultData(): array
    {
        $id = $this->getId();
        $query = "INSERT INTO module_sticker_sizes (width, id_sticker) VALUES
            (20, $id),
            (50, $id),
            (100, $id), 
            (150, $id), 
            (200, $id),
            (250, $id), 
            (300, $id),
            (400, $id),
            (500, $id), 
            (600, $id),
            (700, $id),
            (800, $id), 
            (900, $id),
            (1000, $id),
            (1100, $id), 
            (1200, $id)";
        DBAccess::insertQuery($query);

        $query = "SELECT id, width, height, price, 
                ((width / 1000) * (height / 1000) * 10) as costs 
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";

        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);
        return $data;
    }

    /**
     * @return array<int, mixed>
     */
    public function getSizes(): array
    {
        $query = "SELECT id, width, height, price, 
                ((width / 1000) * (height / 1000) * 10) as costs, price_default
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";

        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        if ($data == null) {
            $data = $this->generateDefaultData();
        }

        return $data;
    }

    public static function addSize(): void
    {
        $width = (int) Tools::get("width");
        $height = (int) Tools::get("height");
        $price = (int) Tools::get("price");
        $id = (int) Tools::get("idSticker");
        $isDefault = (int) Tools::get("isDefaultPrice");

        $query = "INSERT INTO module_sticker_sizes (width, height, price, id_sticker, price_default) VALUES (:width, :height, :price, :id, :default)";
        $id = DBAccess::insertQuery($query, [
            "width" => $width,
            "height" => $height,
            "price" => $price,
            "id" => $id,
            "default" => $isDefault,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "id" => $id,
        ]);
    }

    public static function deleteSize(): void
    {
        $id = (int) Tools::get("id");
        $query = "DELETE FROM module_sticker_sizes WHERE id = :id;";
        DBAccess::deleteQuery($query, ["id" => $id]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function updateSizes(): void
    {
        $sizes = json_decode(Tools::get("sizes"), true);
        $id = (int) Tools::get("id");
        $aufkleberWandtatto = new AufkleberWandtattoo($id);
        foreach ($sizes as $size) {
            $aufkleberWandtatto->updateSizeTable($size);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success"
        ]);
    }
}
