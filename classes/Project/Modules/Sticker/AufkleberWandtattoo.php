<?php

namespace Classes\Project\Modules\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class AufkleberWandtattoo extends Sticker
{

    protected $basePrice;

    protected $idShopAttributes = [];
    protected $prices = [];
    protected $buyingPrices = [];
    protected $widthsToSizeIds = [];

    public function getPrice($width, $height, $difficulty)
    {
        if ($width >= 1200) {
            $base = 2100;
        } else if ($width >= 900) {
            $base = 1950;
        } else if ($width >= 600) {
            $base = 1700;
        } else if ($width >= 300) {
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

    public function getSize($sizeId)
    {
        return $this->widthsToSizeIds[$sizeId];
    }

    public function getPricesMatched()
    {
        return $this->prices;
    }

    public function updatePrice(int $width, int $height, int $price)
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
     */
    public function updateSizeTable($data)
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

    public function getDifficulty()
    {
        return $this->stickerData["price_class"];
    }

    protected function getDescriptionShortWithDefaultText(): String
    {
        return $this->getSizeTableFormatted() . $this->getDescriptionShort();
    }

    public function getSizeTableFormatted()
    {
        $query = "SELECT width, height
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        foreach ($data as &$d) {
            $d["width"] = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $d["height"] = str_replace(".", ",", ((int) $d["height"]) / 10) . "cm";
        }

        ob_start();
        insertTemplate('classes/Project/Modules/Sticker/Views/sizeTableView.php', [
            "sizes" => $data,
        ]);
        return ob_get_clean();
    }

    public function getBasePrice()
    {
        $basePrice = $this->getBasePriceUnformatted();
        //$this->basePrice = $basePrice;
        return number_format((float) $basePrice, 2, '.', '');
    }

    public function getBasePriceUnformatted()
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
     */
    public function getSizeIds()
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
            $singleSizeInCm = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $sizeId = (int) $this->addAttribute($idAttributeGroup, $singleSizeInCm);
            $sizeIds[] = $sizeId;
            $widths[$sizeId] = $singleSizeInCm;

            $prices[$sizeId] = number_format(($d["price"] / 100 - $this->getBasePrice()) / 1.19, 2);
            $buyingPrices[$sizeId] = $d["costs"];
        }

        $this->idShopAttributes = $sizeIds;
        $this->prices = $prices;
        $this->buyingPrices = $buyingPrices;
        $this->widthsToSizeIds = $widths;
        return $sizeIds;
    }

    protected function addAttribute($attributeGroupId, $attributeName)
    {
        try {
            $xml = $this->getXML('product_option_values?filter[id_attribute_group]=' . $attributeGroupId . '&filter[name]=' . $attributeName . '&limit=1');
            $resources = $xml->children()->children();

            if (!empty($resources)) {
                $attributes = $resources->product_option_value->attributes();
                return $attributes['id'];
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
            $id = $this->xml->product->id;
            return $id;
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    protected function generateDefaultData()
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

    public function getSizes()
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

    public static function addSize()
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

    public static function deleteSize()
    {
        $id = (int) Tools::get("id");
        $query = "DELETE FROM module_sticker_sizes WHERE id = :id;";
        DBAccess::deleteQuery($query, ["id" => $id]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function updateSizes()
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
