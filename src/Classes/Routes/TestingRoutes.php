<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class TestingRoutes extends Routes
{
    protected static $getRoutes = [
        "/test/colors" => [\Src\Classes\Project\Test::class, "migrateFarbenToColor"],
        "/test/table" => [\Src\Classes\Project\Test::class, "table"],
        "/test/config" => [\Src\Classes\Project\Test::class, "configTest"],
        "/test/mail" => [\Src\Classes\Project\Test::class, "sendTestMail"],
    ];

}
