<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class VariousRoutes extends Routes
{

    /**
     * @uses \Classes\Project\TemplateController::ajaxGetTemplate()
     * @uses \Classes\Project\Color::renderColorTemplate()
     */
    protected static $getRoutes = [
        "/template/{template}" => [\Classes\Project\TemplateController::class, "ajaxGetTemplate"],
        "/template/colors/render" => [\Classes\Project\Color::class, "renderColorTemplate"],
    ];

    protected static $postRoutes = [];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
