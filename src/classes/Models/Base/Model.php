<?php

namespace Src\Classes\Models\Base;

use Src\Classes\Models\Traits\HasHooks;
use MaxBrennemann\PhpUtilities\DBAccess;

class Model
{
    use HasHooks;

    protected string $tableName;
    protected string $primaryKey = "id";

    /** @var array<string, mixed> */
    public array $fillable = [];

    /** @var string[] */
    protected array $hidden = [];

    /** @var string[] */
    protected array $columns = [];

    final public function __construct(?string $table = null)
    {
        if ($table !== null) {
            $this->tableName = $table;
        }

        $this->loadTableConfig();
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    protected function loadTableConfig(): void
    {
        $config = self::getTableConfig()[$this->tableName] ?? [];

        $this->primaryKey = $config["primaryKey"] ?? $this->primaryKey;
        $this->hooks = $config["hooks"] ?? [];
        $this->fillable = $config["fillable"] ?? $this->fillable;
        $this->columns = $config["columns"] ?? $this->columns;
        $this->hidden = $config["hidden"] ?? $this->hidden;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected static function getTableConfig(): array
    {
        require_once ROOT . "../src/table-config.php";
        return getTableConfig();
    }

    /**
     * @param int|string $id
     * @return array<string>
     */
    public static function find(int|string $id): ?array
    {
        $instance = new static();
        $query = "SELECT * FROM {$instance->tableName} WHERE {$instance->primaryKey} = :id";
        $results = DBAccess::selectQuery($query, ["id" => $id]);
        return $results[0] ?? null;
    }

    /**
     * @return array<int, array<string>>
     */
    public static function all(): array
    {
        $instance = new static();
        return DBAccess::selectAll($instance->tableName);
    }

    /**
     * @param array<string, string> $conditions
     * @return array<int, array<string>>
     */
    public function read(array $conditions = []): array
    {
        $this->triggerHook("beforeRead", [
            "conditions" => &$conditions,
        ]);

        $params = [];
        $query = "SELECT ";

        if (!empty($this->hidden)) {
            $columns = array_filter(
                $this->columns,
                fn ($el) => !in_array($el, $this->hidden)
            );
            $query .= implode(", ", $columns);
        } else {
            $query .= "*";
        }

        $query .= " FROM {$this->tableName}";

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }

            $query .= " WHERE " . implode(" AND ", $where);
        }

        $data = DBAccess::selectQuery($query, $params);
        $this->triggerHook("afterRead", [
            "conditions" => $conditions,
            "results" => &$data,
        ]);

        return $data;
    }

    /**
     * @param string $relatedTable
     * @param string $localKey
     * @param string $foreignKey
     * @param string $joinType
     * @param array<string, string> $conditions
     * @param string $joinName
     * @return array<int, array<string>>
     */
    public function join(
        string $relatedTable,
        string $localKey,
        string $foreignKey,
        string $joinType = "INNER",
        array $conditions = [],
        string $joinName = "",
    ): array {
        $this->triggerHook("beforeJoin", [
            "relatedTable" => $relatedTable,
            "localKey" => $localKey,
            "foreignKey" => $foreignKey,
            "joinType" => $joinType,
            "conditions" => &$conditions,
        ]);

        $config = self::getTableConfig();
        $baseColumns = $config[$this->tableName]["columns"] ?? ["*"];
        $relatedColumns = $config[$this->tableName]["joins"][$joinName]["columns"]
            ?? $config[$relatedTable]["columns"]
            ?? ["*"];
        $hiddenRelated = $config[$relatedTable]["hidden"] ?? [];

        $relatedColumns = array_diff($relatedColumns, $hiddenRelated);

        $selectColumns = [];
        foreach ($baseColumns as $col) {
            $selectColumns[] = "{$this->tableName}.{$col}";
        }
        foreach ($relatedColumns as $col) {
            $selectColumns[] = "{$relatedTable}.{$col}";
        }

        $selectString = implode(", ", $selectColumns);
        $onClause = "{$this->tableName}.{$localKey} = {$relatedTable}.{$foreignKey}";

        $whereQuery = "";
        $parameters = [];
        if (!empty($conditions)) {
            $whereClauses = [];
            $parameters = [];

            foreach ($conditions as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}";
                $parameters[$key] = $value;
            }

            $whereQuery = implode(" AND ", $whereClauses);
        }

        $query = "SELECT {$selectString}
            FROM {$this->tableName}
            {$joinType} JOIN {$relatedTable}
            ON {$onClause}
            WHERE {$whereQuery}";

        $this->triggerHook("modifyJoinQuery", ["query" => &$query]);

        $results = DBAccess::selectQuery($query, $parameters);

        $this->triggerHook("afterJoin", [
            "results" => &$results,
            "query" => $query,
        ]);

        return $results;
    }

    /**
     * @param array<string, string> $conditions
     * @return int|false
     */
    public function add(array $conditions): int|false
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
            if ($key == $this->primaryKey) {
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

    /**
     * @param array<string, string> $conditions
     * @return bool
     */
    public function delete(array $conditions): bool
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

        DBAccess::deleteQuery($query, $params);

        $this->triggerHook("afterDelete", $conditions);

        return true;
    }

    /**
     * @param int $id
     * @param array<mixed, mixed> $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $this->triggerHook("beforeUpdate", $data);

        $fields = array_intersect_key($data, array_flip($this->fillable));
        $assignments = array_map(fn ($field) => "$field = :$field", array_keys($fields));

        $query = "UPDATE {$this->tableName} SET " . implode(", ", $assignments) . " WHERE {$this->primaryKey} = :id;";
        $fields["id"] = $id;

        $result = DBAccess::updateQuery($query, $fields);

        $this->triggerHook("afterUpdate", $data);

        return $result;
    }
}
