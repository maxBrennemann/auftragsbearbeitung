<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class NotesRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Project\Auftrag::getNotes()
     * @uses \Src\Classes\Project\Auftrag::getNote()
     */
    protected static $getRoutes = [
        "/notes/{orderId}" => [\Src\Classes\Project\Auftrag::class, "getNotes"],
        "/notes/{orderId}/{id}" => [\Src\Classes\Project\Auftrag::class, "getNote"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::addNote()
     * @uses \Src\Classes\Project\Step::insertStepAjax()
     */
    protected static $postRoutes = [
        "/notes/{orderId}" => [\Src\Classes\Project\Auftrag::class, "addNote"],
        "/notes/step/{orderId}" => [\Src\Classes\Project\Step::class, "insertStepAjax"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::updateNote()
     * @uses \Src\Classes\Project\Step::updateStep()
     */
    protected static $putRoutes = [
        "/notes/{id}" => [\Src\Classes\Project\Auftrag::class, "updateNote"],
        "/notes/step/{id}" => [\Src\Classes\Project\Step::class, "updateStep"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::deleteNote()
     * @uses \Src\Classes\Project\Step::deleteStep()
     */
    protected static $deleteRoutes = [
        "/notes/{id}" => [\Src\Classes\Project\Auftrag::class, "deleteNote"],
        "/notes/step/{id}" => [\Src\Classes\Project\Step::class, "deleteStep"],
    ];
}
