<?php

namespace Src\Classes\Controller;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

class ProjectController
{
    public static function getOpenProjects(): void
    {

    }

    public static function getOpenTasks(): void
    {
        $projectId = (int) Tools::get("id");
    }
}
