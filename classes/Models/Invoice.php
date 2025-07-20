<?php

namespace Classes\Models;

class Invoice extends Model
{
    public array $fillable = [];
    protected string $tableName = "invoice";
    protected string $primary = "id";
    protected array $hooks = [];

    public function __construct() {
        parent::__construct($this->hooks);
    }
}