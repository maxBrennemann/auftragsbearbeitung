<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TableRoutes extends Routes
{
    /**
     * @uses \Classes\Table\Table::readData()
     */
    protected static $getRoutes = [
        "/tables/{tablename}" => [\Classes\Table\Table::class, "readData"],
    ];

    /**
     * @uses \Classes\Table\Table::createData()
     */
    protected static $postRoutes = [
        "/tables/{tablename}" => [\Classes\Table\Table::class, "createData"],
    ];

    /**
     * @uses \Classes\Table\Table::updateData()
     */
    protected static $putRoutes = [
        "/tables/{tablename}" => [\Classes\Table\Table::class, "updateData"],
    ];

    /**
     * @uses \Classes\Table\Table::deleteData()
     */
    protected static $deleteRoutes = [
        "/tables/{tablename}" => [\Classes\Table\Table::class, "deleteData"],
    ];

}
