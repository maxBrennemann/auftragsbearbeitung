<?php

namespace Classes\Project\Models;

use MaxBrennemann\PhpUtilities\DBAccess;

class Model
{

    public array $fillable = [];
    protected string $tableName = "";
    protected string $primary = "id";
    protected array $hooks = [];

    public function add() {}

    public function delete() {}

    public function update($id, array $data): bool
    {
        $this->triggerHook("beforeUpdate", $data);

        $fields = array_intersect_key($data, array_flip($this->fillable));
        $assignments = array_map(fn($field) => "$field = :$field", array_keys($fields));

        $query = "UPDATE {$this->tableName} SET " . implode(", ", $assignments) . " WHERE {$this->primary} = :id;";
        $fields["id"] = $id;

        $result = DBAccess::updateQuery($query, $fields);

        $this->triggerHook("afterUpdate", $data);

        return $result;
    }

    public function find($id): ?array
    {
        $query = "SELECT * FROM {$this->tableName} WHERE {$this->primary} = :id";
        return DBAccess::selectQuery($query, [
            "id" => $id,
        ]);
    }

    public function all()
    {
        return DBAccess::selectAll($this->tableName);
    }

    protected function triggerHook(string $hookName, array $data)
    {
        if (isset($this->hooks[$hookName]) && is_callable($this->hooks[$hookName])) {
            $this->hooks[$hookName]($data);
        }
    }
}
