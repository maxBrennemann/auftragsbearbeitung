<?php

namespace Classes\Front;

use Classes\Link;

class Breadcrumb
{

    private $sublinks = array();

    function __construct()
    {
        $url = $_SERVER["REQUEST_URI"];

        $this->sublinks = Link::parseUri();
        /*$this->sublinks = [
            0 => "Start",
            1 => "Aufkleber",
            2 => "Tolles Produkt"
        ];*/

        //var_dump($this->sublinks);
    }

    public function getBreadcrumbNavigation()
    {
        $html = "<ol itemscope itemtype=\"https://schema.org/BreadcrumbList\">";
        $diffElement = " ›";
        $count = 1;

        foreach ($this->sublinks as $s) {
            if ($count == 1) {
                $diffElement = "";
            } else {
                $diffElement = " ›";
            }

            $html .= $diffElement . "<li itemprop=\"itemListElement\" itemscope itemtype = \"https://schema.org/ListItem\">";
            $html .= "<a itemprop=\"item\" href=\"" . $s["link"] . "\">";
            $html .= "<span itemprop=\"name\">" . $s["text"] . "</span></a>";
            $html .= "<meta itemprop=\"position\" content=\"$count\" />";
            $count++;
        }

        $html .= "</ol>";
        return $html;
    }

    static function getNav()
    {
        $b = new self();
        return $b->getBreadcrumbNavigation();
    }
}
