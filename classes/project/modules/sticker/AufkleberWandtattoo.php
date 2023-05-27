<?php

require_once("classes/project/modules/sticker/Sticker.php");

class AufkleberWandtattoo extends Sticker {

    protected $basePrice;

    protected $idShopAttributes = [];
    protected $prices = [];
    protected $buyingPrices = [];
    protected $widthsToSizeIds = [];

    public function getPrice($width, $height, $difficulty) {
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

    public function getSize($sizeId) {
        return $this->widthsToSizeIds[$sizeId];
    }

    public function getPricesMatched() {
        return $this->prices;
    }

    public function updatePrice(int $width, int $height, int $price) {
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
    public function updateSizeTable($data) {
        $width = (int) $data["width"];
        $height = (int) $data["height"];
        
        $currentPrice = $this->getPrice($width, $height, $this->getDifficulty());

        $query = "UPDATE module_sticker_sizes SET height = $height, price = NULL WHERE id_sticker = :id AND width = $width";
        
        StickerChangelog::log($this->getId(), 0, 0, "module_sticker_sizes", "height", $height);
        echo "preis: " . $currentPrice . " " . $data["price"] . " ";

        if ($currentPrice != $data["price"]) {
            $price = $data["price"];
            $query = "UPDATE module_sticker_sizes SET height = $height, price = $price WHERE id_sticker = :id AND width = $width";
            StickerChangelog::log($this->getId(), 0, 0, "module_sticker_sizes", "price", $price);
        }

        DBAccess::updateQuery($query, ["id" => $this->getId()]);
    }

    public function getDifficulty() {
        return $this->stickerData["price_class"];
    }

    protected function getDescriptionShortWithDefaultText(): String {
        return $this->getSizeTableFormatted() . $this->getDescriptionShort();
    }

    public function getSizeTableFormatted() {
        // TODO: eventuell size_summary im BO generieren oder direkt die Tabelle exportieren
        return $this->stickerData["size_summary"];
    }

    public function getBasePrice() {
        if ($this->basePrice != null) {
            return number_format((float) $this->basePrice / 100, 2, '.', '');
        }

        parent::getBasePrice();

        $query = "SELECT price FROM module_sticker_sizes WHERE id_sticker = :idSticker ORDER BY price ASC LIMIT 1;";
        $params = ["idSticker" => $this->idSticker];
        $result = DBAccess::selectQuery($query, $params);

        if ($result == null) {
            $result[] = ["price" => "1000"];
        }
        
        return number_format((float) $result[0]["price"] / 100, 2, '.', '');
    }

    /**
     * gibt die ids der id_attribute_group 5 (Breite) zurück,
     * dabei wird geprüft, ob zu dem Breitenwert schon eine id_attribute existiert und falls nicht,
     * wird diese erstellt
     */
    public function getSizeIds() {
        $query = "SELECT `price`, `width`, ((`width` / 1000) * (`height` / 1000) * 10) as `costs` FROM `module_sticker_sizes` WHERE `id_sticker` = :idSticker ORDER BY `width`";
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        /* TODO: hardcoded idAttributeGroup entfernen */
        $idAttributeGroup = 5;
        $sizeIds = [];
        $widths =  [];
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

    protected function addAttribute($attributeGroupId, $attributeName) {
        try {
            $xml = $this->getXML('product_option_values?filter[id_attribute_group]=' . $attributeGroupId . '&filter[name]=' . $attributeName . '&limit=1');
            $resources = $xml->children()->children();

            if (!empty($resources)) {
                $attributes = $resources->product_option_value->attributes();
                return $attributes['id'];
            }
        } catch(PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
        
        try {
            $xml = $this->getXML('/api/product_options?schema=synopsis');
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
        } catch(PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    protected function generateDefaultData() {
        $id = $this->getId();
        $query = "INSERT INTO module_sticker_sizes (width, id_sticker) VALUES (100, $id), (150, $id), (200, $id), (300, $id), (600, $id), (900, $id), (1200, $id)";
        DBAccess::insertQuery($query);
        
        $query = "SELECT id, width, height, price, 
                ((width / 1000) * (height / 1000) * 10) as costs 
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";
        
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);
        return $data; 
    }

    public function getSizeTable() {
        $query = "SELECT id, width, height, price, 
                ((width / 1000) * (height / 1000) * 10) as costs 
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";
        
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        if ($data == null) {
            $data = $this->generateDefaultData();
        }

        $column_names = array(
            0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
            1 => array("COLUMN_NAME" => "width", "ALT" => "Breite"),
            2 => array("COLUMN_NAME" => "height", "ALT" => "Höhe"),
            3 => array("COLUMN_NAME" => "price", "ALT" => "Preis (brutto)"),
            4 => array("COLUMN_NAME" => "costs", "ALT" => "Material"),
        );

        foreach ($data as &$d) {
            $d["width"] = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $d["height"] = str_replace(".", ",", ((int) $d["height"]) / 10) . "cm";
            $d["price"] = number_format((float) $d["price"] / 100, 2, ',', '') . "€";
            $d["costs"] = number_format((float) $d["costs"], 2, ',', '') . "€";
        }

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->setType("module_sticker_sizes");
		$t->addActionButton("delete", "id");
		$t->addNewLineButton();
        $t->addAction(null, Icon::$iconReset, "Preis zurücksetzen");

        $pattern = [
            "id_sticker" => [
                "status" => "preset",
                "value" => $this->getId(),
            ],
            "width" => [
                "status" => "unset",
                "value" => 1,
                "type" => "cm",
                "cast" => [],
            ],
            "height" => [
                "status" => "unset",
                "value" => 2,
                "type" => "cm",
                "cast" => [],
            ],
            "price" => [
                "status" => "unset",
                "value" => 3,
                "type" => "float",
                "cast" => ["separator" => ","],
                "default" => null,
            ],
        ];

		$t->defineUpdateSchedule(new UpdateSchedule("module_sticker_sizes", $pattern));
        $_SESSION[$t->getTableKey()] = serialize($t);
		return $t->getTable();
    }

}

?>