<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'tagsOverview.php', 'Tag-Übersicht', 'tags-overview');",
        "DELIMITER //

        CREATE TRIGGER create_sizes
        AFTER INSERT ON module_sticker_sticker_data
        FOR EACH ROW
        BEGIN
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price)
          VALUES (NEW.id, 100, 0, 0);
        
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price)
          VALUES (NEW.id, 200, 0, 0);
        
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price)
          VALUES (NEW.id, 300, 0, 0);
        
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price)
          VALUES (NEW.id, 600, 0, 0);
        
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price)
          VALUES (NEW.id, 900, 0, 0);
        
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price)
          VALUES (NEW.id, 1200, 0, 0);
        END//
        
        DELIMITER ;",
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