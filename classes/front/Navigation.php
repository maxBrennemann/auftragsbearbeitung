<?php

require_once('classes/DBAccess.php');

class Navigation {

    private $types = [
        "top" => 1
    ];

    private $link = "";
    private $name = "";
    
    function __construct($link, $name) {
        $this->link = $link;
        $this->name = $name;
    }

    function getItemLink() {
        return $this->link;
    }

    function getItemName() {
        return $this->name;
    }

    static function getNavigationLinks($type) {
        $items = array();

        if ($type == "top") {
            $query = "SELECT * FROM navigation WHERE type = 1";
            $data = DBAccess::selectQuery($query);

            foreach ($data as $d) {
                /* type 1 is category link */
                if ($d["type"] == 1) {
                    $id = $d["link"];
                    $query_category = "SELECT * FROM category WHERE id = $id";
                    $data_category = DBAccess::selectQuery($query_category);

                    $name = $data_category[0]["name"];
                }

                $link = "";

                $item = new Navigation($link, $name);

                array_push($items, $item);
            }
        }

        return $items;
    }

    static function getFooterLinks() {
        return DBAccess::selectQuery("SELECT * FROM footer_links");
    }

}

?>