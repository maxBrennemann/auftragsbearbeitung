<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Offer {

    function __construct() {

    }

    public static function getOpenOffers() {
        $query = "SELECT * FROM offer WHERE `state` = 'created';";
        $data = DBAccess::selectQuery($query);

        return $data;
    }

    public static function getAllOffers() {

    }

}
