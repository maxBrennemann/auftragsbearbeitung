<?php

require_once('classes/project/Produkt.php');

class ProductController extends Produkt {

    private $quantity = 0;

    function __construct($productId) {
        parent::__construct($productId);
    }

    public function setQuantity($newQty) {
        $newQty = (int) $newQty;
        if ($newQty > 0) {
            $this->quantity = $newQty;
        }
    }

}

?>