<?php

namespace Classes\Models;

class TaskExecutions extends Model {

    public array $fillable = [];
    protected string $tableName = "task_executions";
    protected string $primary = "id";
    protected array $hooks = [];
    protected array $hidden = [];
    protected array $columns = [];

    public function __construct()
    {
        
    }
}
