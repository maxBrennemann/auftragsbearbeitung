<?php

return new class () {
    private $queries = [
        "ALTER TABLE `invoice_layout` ADD UNIQUE(`invoice_id`, `content_type`, `content_id`);",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
