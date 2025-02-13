<?php

return new class {

    private $queries = [
        "DROP TRIGGER IF EXISTS create_sizes;",
        "DELIMITER //
        CREATE TRIGGER create_sizes
        AFTER INSERT ON module_sticker_sticker_data
        FOR EACH ROW
        BEGIN
          INSERT INTO module_sticker_sizes (id_sticker, width, height, price) VALUES 
          (NEW.id, 20, 0, 0),
          (NEW.id, 50, 0, 0),
          (NEW.id, 100, 0, 0),
          (NEW.id, 150, 0, 0),
          (NEW.id, 200, 0, 0),
          (NEW.id, 250, 0, 0),
          (NEW.id, 300, 0, 0),
          (NEW.id, 400, 0, 0),
          (NEW.id, 500, 0, 0),
          (NEW.id, 600, 0, 0),
          (NEW.id, 700, 0, 0),
          (NEW.id, 800, 0, 0),
          (NEW.id, 900, 0, 0),
          (NEW.id, 1000, 0, 0),
          (NEW.id, 1100, 0, 0),
          (NEW.id, 1200, 0, 0);
        END//
        DELIMITER ;",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
