<?php

namespace Src\Classes\Sticker;

use Src\Classes\Protocol;
use SimpleXMLElement;

class StickerCombination extends PrestashopConnection
{
    private Sticker $sticker;
    private bool $defaultOn = true;

    /** @var array<int, mixed> */
    private array $arguments;
    /** @var array<int, mixed> */
    private array $attributes = [];
    /** @var array<int, mixed> */
    private array $prices = [];
    /** @var array<int, mixed> */
    private array $purchasingPrices = [];

    public function __construct(Sticker $sticker)
    {
        parent::__construct();

        $this->sticker = $sticker;
    }

    /**
     * @return array<int, mixed>
     */
    public function getAttributeCombinations(): array
    {
        $this->attributes = $this->sticker->getAttributes();
        $this->arguments = $this->combine($this->attributes);
        return $this->arguments;
    }

    public function createCombinations(): void
    {
        // TODO: getAttributes, getPurchasingPrices and getPrices implementieren
        $this->attributes = $this->sticker->getAttributes();
        $this->prices = $this->sticker->getPricesMatched();
        $this->purchasingPrices = $this->sticker->getPurchasingPricesMatched() ?: [];

        $this->arguments = $this->combine($this->attributes);

        try {
            $xml = $this->getXML('combinations?schema=blank');

            foreach ($this->arguments as $arg) {
                $this->addCombination($xml, $arg);
            }
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }

        $this->setStockAvailables();
    }

    /**
     * @param int $productId
     * @return SimpleXMLElement
     */
    private function getOldCombinations(int $productId): ?SimpleXMLElement
    {
        $productId = (int) $productId;
        if ($productId == 0) {
            return null;
        }

        try {
            $xml = $this->getXML("products/$productId");
            $product = $xml->children()->children();

            return $product->associations->combinations;
        } catch (\PrestaShopWebserviceException $e) {
            Protocol::write($e->getMessage());
            return null;
        }
    }

    public function removeOldCombinations(int $productId): void
    {
        $productId = (int) $productId;
        if ($productId == 0) {
            return;
        }

        $productCombinations = $this->getOldCombinations($productId);

        if (count($productCombinations) == 0) {
            return;
        }

        foreach ($productCombinations->combination as $combination) {
            $combinationId = (int) $combination->{"id"};
            $this->deleteXML("combinations", $combinationId);
        }
    }

    /**
     * @param array<int, mixed> $elements
     * @return array<int, mixed>
     */
    private function combine(array $elements): array
    {
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

    /**
     * @param mixed $xml
     * @param array<mixed, mixed> $args
     * @return int
     */
    private function addCombination(mixed $xml, array $args): int
    {
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

            $id = (int) $this->xml->combination->id;
            return $id;
        } catch (\PrestaShopWebserviceException $e) {
            echo $e;
        }

        return 0;
    }

    /**
     * @return array<int, int>
     */
    private function getStockAvailablesIds(): array
    {
        $stockAvailablesIds = [];

        try {
            $idProduct = (int) $this->sticker->getIdProduct();

            if ($idProduct == 0) {
                return $stockAvailablesIds;
            }

            $xml = $this->getXML('products/' . $idProduct);
            $stocks = $xml->children()->children()->associations->stock_availables;

            foreach ($stocks->stock_available as $stock) {
                $id = (int) $stock->id;
                array_push($stockAvailablesIds, $id);
            }
        } catch (\PrestaShopWebserviceException $e) {
            Protocol::write($e->getMessage());
        }

        return $stockAvailablesIds;
    }

    private function setStockAvailables(int $quantity = 20): void
    {
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
            } catch (\PrestaShopWebserviceException $e) {
                echo $e;
            }
        }
    }
}
