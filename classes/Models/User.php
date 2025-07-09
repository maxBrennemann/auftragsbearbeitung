<?php

namespace Classes\Models;

class User extends Model
{
    public array $fillable = [];
    protected string $tableName = "user";
    protected string $primary = "id";
    protected array $hooks = [];
    protected array $hidden = [];
    protected array $columns = [];

    public function __construct()
    {

    }
}
