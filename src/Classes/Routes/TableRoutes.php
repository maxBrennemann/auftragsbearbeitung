<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class TableRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Table\Table::readData()
     */
    protected static $getRoutes = [
        "/tables/{tablename}" => [\Src\Classes\Table\Table::class, "readData"],
    ];

    /**
     * @uses \Src\Classes\Table\Table::createData()
     */
    protected static $postRoutes = [
        "/tables/{tablename}" => [\Src\Classes\Table\Table::class, "createData"],
    ];

    /**
     * @uses \Src\Classes\Table\Table::updateData()
     */
    protected static $putRoutes = [
        "/tables/{tablename}" => [\Src\Classes\Table\Table::class, "updateData"],
    ];

    /**
     * @uses \Src\Classes\Table\Table::deleteData()
     */
    protected static $deleteRoutes = [
        "/tables/{tablename}" => [\Src\Classes\Table\Table::class, "deleteData"],
    ];

}
