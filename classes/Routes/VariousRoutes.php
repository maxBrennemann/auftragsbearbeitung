<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class VariousRoutes extends Routes
{

    /**
     * @uses \Classes\Project\TemplateController::ajaxGetTemplate()
     */
    protected static $getRoutes = [
        "/template/{template}" => [\Classes\Project\TemplateController::class, "ajaxGetTemplate"],
    ];

    protected static $postRoutes = [];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
