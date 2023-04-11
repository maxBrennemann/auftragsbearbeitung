<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "INSERT INTO module_sticker_exports (idSticker, facebook, google, amazon, etsy, ebay, pinterest)
        SELECT module_sticker_sticker_data.id, -1, -1, -1, -1, -1, -1
        FROM module_sticker_sticker_data
        WHERE NOT EXISTS (
          SELECT 1
          FROM module_sticker_exports
          WHERE module_sticker_exports.idSticker = module_sticker_sticker_data.id
        );",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        /* TODO: downgrade schreiben */
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>