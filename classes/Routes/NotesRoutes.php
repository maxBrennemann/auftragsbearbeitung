<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class NotesRoutes extends Routes
{
    /**
     * @uses \Classes\Project\Auftrag::getNotes()
     * @uses \Classes\Project\Auftrag::getNote()
     */
    protected static $getRoutes = [
        "/notes/{orderId}" => [\Classes\Project\Auftrag::class, "getNotes"],
        "/notes/{orderId}/{id}" => [\Classes\Project\Auftrag::class, "getNote"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::addNote()
     * @uses \Classes\Project\Step::insertStepAjax()
     */
    protected static $postRoutes = [
        "/notes/{orderId}" => [\Classes\Project\Auftrag::class, "addNote"],
        "/notes/step/{orderId}" => [\Classes\Project\Step::class, "insertStepAjax"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::updateNote()
     */
    protected static $putRoutes = [
        "/notes/{id}" => [\Classes\Project\Auftrag::class, "updateNote"],
    ];

    /**
     * @uses \Classes\Project\Auftrag::deleteNote()
     */
    protected static $deleteRoutes = [
        "/notes/{id}" => [\Classes\Project\Auftrag::class, "deleteNote"],
    ];
}
