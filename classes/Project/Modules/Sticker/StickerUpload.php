<?php

namespace Classes\Project\Modules\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;

class StickerUpload extends PrestashopConnection
{

    private $idSticker;
    private $idProduct;
    private $idCategoryPrimary = 2;
    private $title;
    private $basePrice;
    private $description;
    private $descriptionShort;
    private $categories;

    public function __construct($idSticker, $title, $basePrice, $description, $descriptionShort)
    {
        parent::__construct();

        $this->idSticker = $idSticker;
        $this->title = $title;
        $this->basePrice = $basePrice;
        $this->description = $description;
        $this->descriptionShort = $descriptionShort;
    }

    public function setIdProduct($idProduct)
    {
        $this->idProduct = $idProduct;
    }

    public function setIdCategoryPrimary($idCategoryPrimary)
    {
        $this->idCategoryPrimary = $idCategoryPrimary;
    }

    public function setCategoires($categories)
    {
        $this->categories = $categories;
        $this->setCategory();
    }

    public function createSticker()
    {
        $this->create();
        return $this->idProduct;
    }

    public function updateSticker($idProduct)
    {
        $this->setIdProduct($idProduct);
        $this->update();
    }

    private function update()
    {
        if ($this->idProduct == 0) {
            return;
        }

        try {
            $xml = $this->getXML("products/" . $this->idProduct);
            $this->manipulateProductXML($xml);

            //$stickerTagManager = new StickerTagManager($this->idSticker, $this->title);
            //$stickerTagManager->setProductId($this->idProduct);
            //$stickerTagManager->saveTagsXml($xml);

            $resource_product = $xml->children()->children();
            $resource_product->{"id"} = $this->idProduct;

            $opt = array(
                'resource' => 'products',
                'putXml' => $xml->asXML(),
                'id' => $this->idProduct,
            );
            $this->editXML($opt);
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    private function create()
    {
        try {
            $xml = $this->getXML('products?schema=blank');
            $this->manipulateProductXML($xml);

            //$stickerTagManager = new StickerTagManager($this->idSticker, $this->title);
            //$stickerTagManager->setProductId($this->idProduct);
            //$stickerTagManager->saveTagsXml($xml);

            $opt = array(
                'resource' => 'products',
                'postXml' => $xml->asXML(),
            );
            $this->addXML($opt);
            $this->idProduct = (int) $this->xml->product->id;
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }

    /* https://www.prestashop.com/forums/topic/640693-how-to-add-a-product-through-the-webservice-with-custom-feature-values/#comment-2663527 */
    private function manipulateProductXML(&$xml)
    {
        $resource_product = $xml->children()->children();

        /* unset unused paramters */
        unset($resource_product->id);
        unset($resource_product->position_in_category);
        unset($resource_product->manufacturer_name);
        unset($resource_product->id_default_combination);
        unset($resource_product->associations);
        unset($resource_product->associations->categories);
        unset($resource_product->associations->images);
        unset($resource_product->associations->combinations);
        unset($resource_product->associations->product_option_values);
        unset($resource_product->associations->stock_availables);
        unset($resource_product->quantity);
        unset($resource_product->position_in_category);

        /* set necessary parameters */
        $resource_product->{'id_shop'} = 1;
        $resource_product->{'minimal_quantity'} = 1;
        $resource_product->{'available_for_order'} = 1;
        $resource_product->{'show_price'} = 1;
        $resource_product->{'id_category_default'} = $this->idCategoryPrimary;
        $resource_product->{'id_tax_rules_group'} = 8; /* Steuergruppennummer für DE 19% */
        $resource_product->{'price'} = number_format(($this->basePrice) / 1.19, 2);
        $resource_product->{'active'} = 1;
        $resource_product->{'reference'} = $this->idSticker;
        $resource_product->{'visibility'} = 'both';
        $resource_product->{'name'}->language[0] = $this->title;
        $resource_product->{'description'}->language[0] = $this->description;
        $resource_product->{'description_short'}->language[0] = $this->descriptionShort;
        $resource_product->{'state'} = 1;
    }

    private function setCategory($unset = false)
    {
        if ($this->idProduct == 0) {
            return;
        }

        try {
            $xml = $this->getXML("products/" . $this->idProduct);
            $product = $xml->children()->children();

            if ($unset) {
                unset($product->associations->categories);
            }

            /*
             * Unset fields that may not be updated, without this, a 400 bad request happens
             * https://stackoverflow.com/questions/36883467/how-can-i-update-product-categories-using-prestashop-web-service
             */
            unset($product->manufacturer_name);
            unset($product->quantity);

            $categories = $product->associations->addChild('categories');

            foreach ($this->categories as $id) {
                $category = $categories->addChild("category");
                $category->addChild("id", $id);
            }

            $opt = array(
                'resource' => 'products',
                'putXml' => $xml->asXML(),
                'id' => $this->idProduct,
            );
            $this->editXML($opt);
        } catch (\PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
    }
}
