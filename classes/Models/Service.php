<?php

namespace Classes\Models;

use Classes\Project\Models\Model;

class Service extends Model {

    public array $fillable = [];
    protected string $tableName = "leistungen";
    protected string $primary = "id";
    protected array $hooks = [];
    protected array $hidden = [];
    protected array $columns = [];

    public function __construct()
    {
        
    }
}
