<?php

require_once("classes/routes/Routes.php");

class NotesRoutes extends Routes {

    protected static $getRoutes = [
        "/notes" => "Notes::getNotes",
        "/notes/{id}" => "Notes::getNote",
    ];

    protected static $postRoutes = [
        "/notes/add" => "Notes::addNote",
    ];

    protected static $putRoutes = [
        "/notes/{id}" => "Notes::updateNote",
    ];

    protected static $deleteRoutes = [
        "/notes/{id}" => "Notes::deleteNote",
    ];

    public function __construct() {
        parent::__construct();
    }

}
