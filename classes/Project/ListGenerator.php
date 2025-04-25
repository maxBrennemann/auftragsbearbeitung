<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Link;

class ListGenerator
{

    private int $id = 0;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
