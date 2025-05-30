<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class VariousRoutes extends Routes
{

    /**
     * @uses \Classes\Project\TemplateController::ajaxGetTemplate()
     * @uses \Classes\Project\Color::renderColorTemplate()
     * @uses \Classes\Project\Invoice::getAltNamesTemplate()
     */
    protected static $getRoutes = [
        "/template/{template}" => [\Classes\Project\TemplateController::class, "ajaxGetTemplate"],
        "/template/colors/render" => [\Classes\Project\Color::class, "renderColorTemplate"],
        "/template/invoice/alt-names" => [\Classes\Project\Invoice::class, "getAltNamesTemplate"],
    ];

    protected static $postRoutes = [];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
