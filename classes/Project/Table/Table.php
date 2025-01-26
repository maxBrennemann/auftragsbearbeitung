<?php

namespace Classes\Project\Table;

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\Models\Model;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Table extends Model
{

    private static function getTableConfig()
    {
        require_once "config/table-config.php";
        $data = getTableConfig();
        return $data;
    }

    private static function checkPermissions($tableConfig, $action): bool
    {
        if (!array_key_exists("permissions", $tableConfig)) {
            return false;
        }

        if (!in_array($action, $tableConfig["permissions"])) {
            return false;
        }

        return true;
    }

    private static function getConditions(): array
    {
        $conditions = Tools::get("conditions");
        if ($conditions) {
            $conditions = json_decode(($conditions), true);
            if (!is_array($conditions)) {
                JSONResponseHandler::throwError(400, [
                    "error" => "Invalid conditions format",
                ]);
            }
        } else {
            $conditions = [];
        }

        return $conditions;
    }

    public static function readData()
    {
        $table = Tools::get("tablename");
        $config = self::getTableConfig();
        $tableConfig = $config[$table] ?? null;

        if (!$tableConfig) {
            JSONResponseHandler::returnNotFound([
                "error" => "Invalid table namne",
            ]);
        }

        if (!self::checkPermissions($tableConfig, "read")) {
            JSONResponseHandler::throwError(401, "Insufficient permissions");
        }

        $hooks = $tableConfig["hooks"] ?? [];
        $model = new Model($hooks);
        $model->tableName = $table;
        $model->hidden = $tableConfig["hidden"] ?? [];
        $model->columns = $tableConfig["columns"] ?? [];
        $model->fillable = [];

        $conditions = self::getConditions();

        $joins = $tableConfig["joins"] ?? [];
        foreach ($joins as $key => $join) {
            if (Tools::get($key) !== null) {
                $value = Tools::get($key);

                $results = $model->join(
                    $join["relatedTable"],
                    $join["localKey"],
                    $join["foreignKey"],
                    $join["type"] ?? "INNER",
                    array_merge($conditions, [$join["foreignKey"] => $value]),
                );

                JSONResponseHandler::sendResponse($results);
            }
        }

        $results = $model->read($conditions);

        JSONResponseHandler::sendResponse($results);
    }

    public static function createData()
    {
        $table = Tools::get("tablename");
        $config = self::getTableConfig();
        $tableConfig = $config[$table] ?? null;

        if (!$tableConfig) {
            JSONResponseHandler::returnNotFound([
                "error" => "Invalid table namne",
            ]);
        }

        if (!self::checkPermissions($tableConfig, "create")) {
            JSONResponseHandler::throwError(401, "Insufficient permissions");
        }

        $hooks = $tableConfig["hooks"] ?? [];
        $model = new Model($hooks);
        $model->tableName = $table;
        $model->hidden = $tableConfig["hidden"] ?? [];
        $model->columns = $tableConfig["columns"] ?? [];
        $model->fillable = [];

        $conditions = self::getConditions();
        $results = $model->add($conditions);

        JSONResponseHandler::sendResponse($results);
    }

    public static function updateData()
    {
        $table = Tools::get("tablename");
        $config = self::getTableConfig();
        $tableConfig = $config[$table] ?? null;

        if (!$tableConfig) {
            JSONResponseHandler::returnNotFound([
                "error" => "Invalid table namne",
            ]);
        }

        if (!self::checkPermissions($tableConfig, "update")) {
            JSONResponseHandler::throwError(401, "Insufficient permissions");
        }
    }

    public static function deleteData()
    {
        $table = Tools::get("tablename");
        $config = self::getTableConfig();
        $tableConfig = $config[$table] ?? null;

        if (!$tableConfig) {
            JSONResponseHandler::returnNotFound([
                "error" => "Invalid table namne",
            ]);
        }

        if (!self::checkPermissions($tableConfig, "delete")) {
            JSONResponseHandler::throwError(401, "Insufficient permissions");
        }

        $model = new Model($tableConfig["hooks"]);
        $model->tableName = $table;
        $model->fillable = [];

        $conditions = self::getConditions();
        $results = $model->delete($conditions);

        if ($results == false) {
            JSONResponseHandler::throwError(400, [
                "error" => "Invalid deletion",
            ]);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }
}
