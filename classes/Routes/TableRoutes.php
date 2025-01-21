<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class TableRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Table\Table::readData()
     */
    protected static $getRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "readData"],
    ];

    /**
     * @uses \Classes\Project\Table\Table::createData()
     */
    protected static $postRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "createData"],
    ];

    /**
     * @uses \Classes\Project\Table\Table::updateData()
     */
    protected static $putRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "updateData"],
    ];

    /**
     * @uses \Classes\Project\Table\Table::deleteData()
     */
    protected static $deleteRoutes = [
        "/tables/{tablename}" => [\Classes\Project\Table\Table::class, "deleteData"],
    ];

}
