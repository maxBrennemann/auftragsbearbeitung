<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TestingRoutes extends Routes
{
    protected static $getRoutes = [
        "/test/colors" => [\Classes\Project\Test::class, "migrateFarbenToColor"],
        "/test/table" => [\Classes\Project\Test::class, "table"],
    ];

}
