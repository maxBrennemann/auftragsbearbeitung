<?php

require_once("classes/routes/Routes.php");
require_once("classes/project/Auftrag.php");

class NotesRoutes extends Routes {

    /**
     * @uses Auftrag::getNotes
     * @uses Auftrag::getNote
     */
    protected static $getRoutes = [
        "/notes/{orderId}" => "Auftrag::getNotes",
        "/notes/{orderId}/{id}" => "Auftrag::getNote",
    ];

    /**
     * @ueses Auftrag::addNote
     */
    protected static $postRoutes = [
        "/notes/{orderId}" => "Auftrag::addNote",
    ];

    /**
     * @uses Auftrag::updateNote
     */
    protected static $putRoutes = [
        "/notes/{id}" => "Auftrag::updateNote",
    ];

    /**
     * @uses Auftrag::deleteNote
     */
    protected static $deleteRoutes = [
        "/notes/{id}" => "Auftrag::deleteNote",
    ];

    public function __construct() {
        parent::__construct();
    }

}
