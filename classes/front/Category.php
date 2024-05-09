<?php

class Category
{

    public $title;
    public $parent;
    public $id;

    public $children = [];

    function __construct($title, $parent, $id)
    {
        $this->title = $title;
        $this->parent = (int) $parent;
        $this->id = (int) $id;
    }

    public function addChild($categoryNode)
    {
        array_push($this->children, $categoryNode);
    }

    public function getHTML()
    {
        $html = "<li><a href=\"" . Link::getCategoryLink($this->id) . "\">" . $this->title . "</a>";

        foreach ($this->children as $child) {
            $html .= "<ul>" . $child->getHTML() . "</ul>";
        }

        $html .= "</li>";
        return $html;
    }

    public function getOneLayerArray()
    {
        $onelayerarray = array();

        array_push($onelayerarray, $this);

        foreach ($this->children as $child) {
            $pushTo = $child->getOneLayerArray();
            foreach ($pushTo as $p) {
                array_push($onelayerarray, $p);
            }
        }

        return $onelayerarray;
    }
}
