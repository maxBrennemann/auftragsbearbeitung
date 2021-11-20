<?php

require_once('classes/DBAccess.php');
require_once('classes/front/Category.php');

class CategoryTree {
    
    public static function getHTMLRepresentation() {
        $data = self::parseCategoryTree();
        $html = "<ul>";

        foreach ($data as $node) {
            $html .= $node->getHTML();
        }

        $html .= "</ul>";

        return $html;
    }

    public static function getOneLayerArray() {
        $data = self::parseCategoryTree();
        $onelayerarray = array();

        foreach ($data as $node) {
            $pushTo = $node->getOneLayerArray();
            foreach ($pushTo as $p) {
                array_push($onelayerarray, $p);
            }
        }

        return $onelayerarray;
    }

    public static function parseCategoryTree() {
        $data = self::getData();
        $categoryTree = [];
        $remove_buff = [];

        foreach ($data as $c) {
            $categoryTree[$c['id']] = new Category($c['name'], $c['parent'], $c['id']);
        }

        foreach ($categoryTree as $c) {
            if ($c->parent != 0) {
                $categoryTree[$c->parent]->addChild($c);
                array_push($remove_buff, $c);
            }
        }

        foreach ($remove_buff as $remove) {
            unset($categoryTree[$remove->id]);
        }

        return $categoryTree;
    }

    private static function getData() {
        return DBAccess::selectQuery("SELECT * FROM category");
    }

}

?>