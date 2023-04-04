<?php

class StickerCombination extends PrestashopConnection {

    private $sticker;
    private $arguments;
    private $defaultOn = true;
    private $attributes;
    private $prices;
    private $purchasingPrices;

    function __construct(Sticker $sticker) {
        $this->sticker = $sticker;
    }

    public function getAttributeCombinations() {
        $this->attributes = $this->sticker->getAttributes();
        $this->arguments = $this->combine($this->attributes);
        return $this->arguments;
    }

    public function createCombinations() {
        // TODO: getAttributes, getPurchasingPrices and getPrices implementieren
        $this->attributes = $this->sticker->getAttributes();
        $this->prices = $this->sticker->getPricesMatched();
        $this->purchasingPrices = $this->sticker->getPurchasingPricesMatched();

        $this->arguments = $this->combine($this->attributes);

        try {
            $xml = $this->getXML('combinations?schema=blank');

            foreach ($this->arguments as $arg) {
                $this->addCombination($xml, $arg);
            }
        } catch (PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
        
        $this->setStockAvailables();
    }

    private function removeOldCombinations() {
        // TODO: write update or remove functions
    }

    private function combine($elements) {
        $result = [];
        if (sizeof($elements) > 1) {
            $combine = array_shift($elements);
        
            foreach ($combine as $el) {
                $temp = $this->combine($elements);
                foreach ($temp as $t) {
                    array_unshift($t, $el);
                    $result[] = $t;
                }
            }
            return $result;
        } else {
            foreach ($elements[0] as $element) {
                $result[] = [$element];
            }
            return $result;
        }
    }

    private function addCombination($xml, $args) {
        try {
            $combination = $xml->children()->children();
            $combination->id_product = $this->sticker->getIdProduct();

            /* sets the default product combination */
            if ($this->defaultOn) {
                $combination->default_on = 1;
                $this->defaultOn = false;
            } else {
                $combination->default_on = 0;
            }

            $combination->minimal_quantity = 0;
            unset($combination->associations->product_option_values);
            $product_option_values = $combination->associations->addChild("product_option_values");

            foreach ($args as $a) {
                $prodVal = $product_option_values->addChild("product_option_value");
                $prodVal->addChild("id", $a);

                /* füge Preise und Einkaufspreise hinzu */
                if ($this->prices != null && array_key_exists($a, $this->prices)) {
                    $combination->price = $this->prices[$a];
                }

                if ($this->prices != null && array_key_exists($a, $this->purchasingPrices)) {
                    $combination->wholesale_price = $this->purchasingPrices[$a];
                }
            }

            $opt = array(
                'resource' => 'combinations',
                'postXml' => $xml->asXML(),
            );
            $this->addXML($opt);

            return $this->xml->combination->id;
        } catch (PrestaShopWebserviceException $e) {
            echo $e;
        }
    }

    private function getStockAvailablesIds() {
        $stockAvailablesIds = array();

        try {
            $xml = $this->getXML('products/' . (int) $this->sticker->getIdProduct());
            $stocks = $xml->children()->children()->associations->stock_availables;

            foreach ($stocks->stock_available as $stock) {
                array_push($stockAvailablesIds, $stock->id);
            }
        } catch (PrestaShopWebserviceException $e) {
            echo $e;
        }

        return $stockAvailablesIds;
    }

    private function setStockAvailables($quantity = 20) {
        $ids = $this->getStockAvailablesIds();

        foreach ($ids as $id) {
            $id = (int) $id;

            try {
                $xml = $this->getXML('stock_availables/' . $id);
                $stock = $xml->children()->children();

                $stock->{'quantity'} = $quantity;
                $stock->{'id_shop'} = "1";

                $opt = array(
                    'resource' => 'stock_availables',
                    'putXml' => $xml->asXML(),
                    'id' => $id,
                );
                $this->editXML($opt);
            } catch (PrestaShopWebserviceException $e) {
                echo $e;
            }
        }
    }

}

?>