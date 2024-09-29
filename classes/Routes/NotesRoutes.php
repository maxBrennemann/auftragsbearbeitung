<?php

namespace Classes\Routes;

class NotesRoutes extends Routes {

    /**
     * @uses Auftrag::getNotes
     * @uses Auftrag::getNote
     */
    protected static $getRoutes = [
        "/notes/{orderId}" => [\Classes\Project\Auftrag::class, "getNotes"],
        "/notes/{orderId}/{id}" => [\Classes\Project\Auftrag::class, "getNote"],
    ];

    /**
     * @ueses Auftrag::addNote
     */
    protected static $postRoutes = [
        "/notes/{orderId}" => [\Classes\Project\Auftrag::class, "addNote"],
    ];

    /**
     * @uses Auftrag::updateNote
     */
    protected static $putRoutes = [
        "/notes/{id}" => [\Classes\Project\Auftrag::class, "updateNote"],
    ];

    /**
     * @uses Auftrag::deleteNote
     */
    protected static $deleteRoutes = [
        "/notes/{id}" => [\Classes\Project\Auftrag::class, "deleteNote"],
    ];

    public function __construct() {
        parent::__construct();
    }

}
