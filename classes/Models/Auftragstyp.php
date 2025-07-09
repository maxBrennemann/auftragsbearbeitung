<?php

namespace Classes\Models;

class Auftragstyp extends Model
{
    public array $fillable = [];
    protected string $tableName = "auftragstyp";
    protected string $primary = "id";
    protected array $hooks = [];
    protected array $hidden = [];
    protected array $columns = [];

    public function __construct()
    {

    }
}
