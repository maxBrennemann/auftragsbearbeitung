<?php

namespace Classes\Models;

use MaxBrennemann\PhpUtilities\DBAccess;

class Model
{

    public array $fillable = [];
    protected string $tableName = "";
    protected string $primary = "id";
    protected array $hooks = [];
    protected array $hidden = [];
    protected array $columns = [];

    public function __construct(array $hooks)
    {
        $this->hooks = $hooks;
    }

    public static function init(string $tableName): Model
    {
        require_once "config/table-config.php";
        $config = getTableConfig();
        $tableConfig = $config[$tableName] ?? null;

        $hooks = $tableConfig["hooks"] ?? [];
        $model = new Model($hooks);
        $model->tableName = $tableName;
        $model->hidden = $tableConfig["hidden"] ?? [];
        $model->columns = $tableConfig["columns"] ?? [];
        $model->fillable = [];

        return $model;
    }

    protected string $conditions = "";

    public function read(array $conditions = []): array
    {
        $this->triggerHook("beforeRead", [
            "conditions" => &$conditions,
        ]);
        $query = "SELECT * FROM {$this->tableName}";

        if (!empty($this->hidden)) {
            $columns = array_filter(
                $this->columns,
                fn($el) => !in_array($el, $this->hidden ?? [])
            );
            $query = "SELECT " . implode(", ", $columns) . " FROM {$this->tableName}";
        }

        if (!empty($conditions)) {
            $whereClauses = [];
            $params = [];

            foreach ($conditions as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }

            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $data = DBAccess::selectQuery($query, $params ?? []);
        $this->triggerHook("afterRead", [
            "conditions" => $conditions,
            "results" => &$data,
        ]);

        return $data;
    }

    public function join(
        string $relatedTable,
        string $localKey,
        string $foreignKey,
        string $joinType = "INNER",
        array $conditions = [],
    ): array {
        $this->triggerHook("beforeJoin", [
            "relatedTable" => $relatedTable,
            "localKey" => $localKey,
            "foreignKey" => $foreignKey,
            "joinType" => $joinType,
            "conditions" => &$conditions,
        ]);

        $onClause = "{$this->tableName}.{$localKey} = {$relatedTable}.{$foreignKey}";

        foreach ($conditions as $key => $value) {
            $onClause .= " AND {$key} = :{$key}";
            $parameters[$key] = $value;
        }

        $query = "SELECT * FROM {$this->tableName}
            {$joinType} JOIN {$relatedTable}
            ON {$onClause};";

        $this->triggerHook("modifyJoinQuery", ["query" => &$query]);

        $results = DBAccess::selectQuery($query, $parameters);

        $this->triggerHook("afterJoin", [
            "results" => &$results,
            "query" => $query,
        ]);

        return $results;
    }

    public function add($conditions): int
    {
        $this->triggerHook("beforeAdd", [
            "conditions" => &$conditions,
        ]);

        $query = "INSERT INTO {$this->tableName}";

        if (empty($conditions)) {
            return false;
        }

        $params = [];
        $keys = [];
        $columns = [];
        foreach ($conditions as $key => $value) {
            if ($key == $this->primary) {
                continue;
            }

            $keys[] = ":{$key}";
            $columns[] = $key;
            $params[$key] = $value;
        }

        $query .= " (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $keys) . ")";
        $lastInsertId = DBAccess::insertQuery($query, $params);

        $this->triggerHook("afterAdd", [
            "conditions" => $conditions,
            "results" => &$lastInsertId,
        ]);

        return $lastInsertId;
    }

    public function delete($conditions): bool
    {
        $this->triggerHook("beforeDelete", $conditions);

        $query = "DELETE FROM {$this->tableName}";

        if (empty($conditions)) {
            return false;
        }

        $whereClauses = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $whereClauses[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $query .= " WHERE " . implode(" AND ", $whereClauses);

        DBAccess::deleteQuery($query, $params ?? []);

        $this->triggerHook("afterDelete", $conditions);

        return true;
    }

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
            $callback = $this->hooks[$hookName];
            call_user_func($callback, $data);
        }
    }
}
