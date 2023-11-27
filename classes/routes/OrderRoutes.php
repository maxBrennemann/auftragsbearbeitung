<?php

use Routes;

class OrderRoutes extends Routes {

    public function __construct() {
        parent::__construct();
    }

    public function testApiRoute() {
        $this->get("/api/test", function() {
            echo "test";
        });
    }

}

$orderRoutes = new OrderRoutes();
$orderRoutes->testApiRoute();
