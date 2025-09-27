<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class VariousRoutes extends Routes
{
    /**
     * @uses \Classes\Controller\TemplateController::ajaxGetTemplate()
     * @uses \Classes\Project\Color::renderColorTemplate()
     * @uses \Classes\Project\Invoice::getAltNamesTemplate()
     * @uses \Classes\Project\InvoiceLayout::getItemsOrderTemplate()
     * @uses \Classes\Project\Icon::ajaxGet()
     * @uses \Classes\Project\Wiki::ajaxGetText()
     */
    protected static $getRoutes = [
        "/template/{template}" => [\Classes\Controller\TemplateController::class, "ajaxGetTemplate"],
        "/template/colors/render" => [\Classes\Project\Color::class, "renderColorTemplate"],
        "/template/invoice/alt-names" => [\Classes\Project\Invoice::class, "getAltNamesTemplate"],
        "/template/invoice/items-order" => [\Classes\Project\InvoiceLayout::class, "getItemsOrderTemplate"],
        "/template/icon/{icon}" => [\Classes\Project\Icon::class, "ajaxGet"],
        "/template/text/{id}" => [\Classes\Project\Wiki::class, "ajaxGetText"],
    ];

    protected static $postRoutes = [];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
