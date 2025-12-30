<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class VariousRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Controller\TemplateController::ajaxGetTemplate()
     * @uses \Src\Classes\Project\Color::renderColorTemplate()
     * @uses \Src\Classes\Project\Invoice::getAltNamesTemplate()
     * @uses \Src\Classes\Project\InvoiceLayout::getItemsOrderTemplate()
     * @uses \Src\Classes\Project\Icon::ajaxGet()
     * 
     * @uses \Src\Classes\Project\Wiki::ajaxGetText()
     * @uses \Src\Classes\Controller\ManualController::get()
     */
    protected static $getRoutes = [
        "/template/{template}" => [\Src\Classes\Controller\TemplateController::class, "ajaxGetTemplate"],
        "/template/colors/render" => [\Src\Classes\Project\Color::class, "renderColorTemplate"],
        "/template/invoice/alt-names" => [\Src\Classes\Project\Invoice::class, "getAltNamesTemplate"],
        "/template/invoice/items-order" => [\Src\Classes\Project\InvoiceLayout::class, "getItemsOrderTemplate"],
        "/template/icon/{icon}" => [\Src\Classes\Project\Icon::class, "ajaxGet"],

        "/manual/text/{id}" => [\Src\Classes\Project\Wiki::class, "ajaxGetText"],
        "/manual/{pageName}" => [\Src\Classes\Controller\ManualController::class, "get"],
    ];

    /**
     * @uses \Src\Classes\Project\Statistics::dispatcher()
     */
    protected static $postRoutes = [
        "/stats" => [\Src\Classes\Project\Statistics::class, "dispatch"],
    ];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
