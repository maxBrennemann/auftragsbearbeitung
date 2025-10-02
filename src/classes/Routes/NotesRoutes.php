<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

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
     */
    protected static $putRoutes = [
        "/notes/{id}" => [\Src\Classes\Project\Auftrag::class, "updateNote"],
    ];

    /**
     * @uses \Src\Classes\Project\Auftrag::deleteNote()
     */
    protected static $deleteRoutes = [
        "/notes/{id}" => [\Src\Classes\Project\Auftrag::class, "deleteNote"],
    ];
}
