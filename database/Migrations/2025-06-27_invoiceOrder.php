<?php

return new class {

    private $queries = [
        "CREATE TABLE invoice_layout (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT NOT NULL,
            position INT NOT NULL,
            content_type ENUM('item', 'text', 'vehicle') NOT NULL,
            content_id INT NOT NULL,

            FOREIGN KEY (invoice_id) REFERENCES invoice(id)
        );",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
