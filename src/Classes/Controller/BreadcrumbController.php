<?php

namespace Src\Classes\Controller;

use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\Tools;

class BreadcrumbController
{
    public static function createBreadcrumbMenu(string $page, string $pageName): string
    {
        $home = Link::getPageLink("");
        $pageLink = Link::getPageLink($page);
        $idPart = "";

        if (Tools::get("id")) {
            $idPart = "/" . Tools::get("id");
        }

        if ($page == "login") {
            $idPart = "";
        }

        return "<a id=\"home_link\" href=\"$home\" class=\"link-primary\">Home</a>/<a href=\"$pageLink\" class=\"link-primary\">$pageName</a>$idPart";
    }
}
