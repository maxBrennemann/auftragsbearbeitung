<?php

/**
 * stellt allgemeine Stickerfunktionen zur Verfügung, ist die Elternklasse von
 * Aufkleber, Wandtattoo und Textil
 */
class Sticker extends PrestashopConnection {

    protected $idSticker;
    protected $idProduct;

    function __construct() {

    }

    public function getName() {

    }

    public function getId() {

    }

    public function getCreationDate() {

    }
    
    public function getBasePrice() {

    }

    public function getDescription() {

    }

    public function getTags() {

    }

    public function getImages() {

    }

    public function getActiveStatus() {

    }

    public function setName() {

    }

    public function save() {

    }

    public function update() {

    }

    public function delete() {
        $this->deleteXML("products", $this->idProduct);
    }

    /**
     * switches the product active status
     */
    public function toggleActiveStatus() {
        $xml = $this->getXML("product/$this->idProduct");
        $resource_product = $xml->children()->children();
        
        $active = (int) $resource_product->active;
        if ($active == 0) {
            $active = 1;
        } else {
            $active = 0;
        }

        $opt = array(
            'resource' => 'products',
            'putXml' => $xml->asXML(),
            'id' => $this->idProduct,
        );
        $this->editXML($opt);
    }
}

?>
