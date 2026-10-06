<?php

return new class () {
    private $queries = [
        "CREATE TABLE invoice_reminder (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT NOT NULL,
            level TINYINT NOT NULL,
            sent_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
