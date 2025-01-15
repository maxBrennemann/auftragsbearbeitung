<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TableRoutes extends Routes
{

    protected static $getRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "readData"],
    ];

    protected static $postRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "createData"],
    ];

    protected static $putRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "updateData"],
    ];

    protected static $deleteRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "deleteData"],
    ];

}
