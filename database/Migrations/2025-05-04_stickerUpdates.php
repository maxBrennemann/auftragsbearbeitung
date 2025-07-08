<?php

return new class () {
    private $queries = [
        "UPDATE module_sticker_image SET image_sort = 'aufkleber' WHERE image_sort = '';",
        "UPDATE module_sticker_image SET image_sort = 'aufkleber' WHERE image_sort = 'general';",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
