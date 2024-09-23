<?php

namespace Classes\Front;

class ProductController extends Produkt {

    private $quantity = 0;

    function __construct($productId) {
        parent::__construct($productId);
        $this->quantity = 1;
    }

    public function setQuantity($newQty) {
        $newQty = (int) $newQty;
        if ($newQty > 0) {
            $this->quantity = $newQty;
        }
    }

    public function incrementQuantity() {
        $this->quantity++;
    }

    public function getQuantity() {
        return $this->quantity;
    }

}
