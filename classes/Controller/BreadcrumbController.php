<?php

namespace Classes\Controller;

use Classes\Link;
use MaxBrennemann\PhpUtilities\Tools;

class BreadcrumbController
{

    public static function createBreadcrumbMenu($page, $pageName): string
    {
        $home = Link::getPageLink("");
        $pageLink = Link::getPageLink($page);
        $idPart = "";

        if (Tools::get("id")) {
            $idPart = "/" . Tools::get("id");
        }

        return "<a href=\"$home\" class=\"link-primary\">Home</a>/<a href=\"$pageLink\" class=\"link-primary\">$pageName</a>$idPart";
    }
}
