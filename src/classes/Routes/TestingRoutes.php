<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TestingRoutes extends Routes
{
    protected static $getRoutes = [
        "/test/colors" => [\Src\Classes\Project\Test::class, "migrateFarbenToColor"],
        "/test/table" => [\Src\Classes\Project\Test::class, "table"],
    ];

}
