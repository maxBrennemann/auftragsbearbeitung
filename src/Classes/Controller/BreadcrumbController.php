<?php

namespace Src\Classes\Controller;

use Src\Classes\Link;
use Src\Classes\Project\Icon;
use MaxBrennemann\PhpUtilities\Tools;

class BreadcrumbController
{
    /**
     * Pfad für die Kopfzeile: „Startseite › Seite › ID“. Das letzte Element ist die aktuelle Seite.
     * #home_link wird von orderManager.ts benutzt (Rücksprung nach dem Löschen) und muss erhalten bleiben.
     */
    public static function createBreadcrumbMenu(string $page, string $pageName): string
    {
        $home = Link::getPageLink("");
        $separator = Icon::get("chevron-right", 14);
        $id = $page == "login" ? null : Tools::get("id");

        $items = ["<a id=\"home_link\" href=\"$home\">Startseite</a>"];

        if ($page != "") {
            $pageLink = Link::getPageLink($page);
            $label = htmlspecialchars($pageName !== "" ? $pageName : ucfirst($page));
            $items[] = $id
                ? "<a href=\"$pageLink\">$label</a>"
                : "<span aria-current=\"page\">$label</span>";
        } else {
            $items[0] = "<span aria-current=\"page\" id=\"home_link\" data-href=\"$home\">Startseite</span>";
        }

        if ($id) {
            $items[] = "<span aria-current=\"page\">" . htmlspecialchars((string) $id) . "</span>";
        }

        return implode($separator, $items);
    }
}
