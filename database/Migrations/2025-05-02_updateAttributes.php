<?php

return new class () {
    private $queries = [
        "UPDATE attribute
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY attribute_group_id ORDER BY id) AS rn
                FROM attribute
            ) AS numbered ON attribute.id = numbered.id
            SET attribute.position = numbered.rn;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
