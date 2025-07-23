<?php

namespace Classes\Table;

use Classes\Models\Model;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Table extends Model
{
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

    private static function getConditions($joins = []): array
    {
        $conditions = Tools::get("conditions");
        if ($conditions) {
            $conditions = json_decode(($conditions), true);
            if (!is_array($conditions)) {
                JSONResponseHandler::throwError(400, "Invalid conditions format");
            }
        } else {
            $conditions = [];
        }

        foreach ($joins as $key => $value) {
            if (array_key_exists($key, $conditions)) {
                unset($conditions[$key]);
            }
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

        $model = new Model($table);

        $joins = $tableConfig["joins"] ?? [];
        $conditions = self::getConditions($joins);

        foreach ($joins as $key => $join) {
            if (Tools::get($key) !== null) {
                $results = $model->join(
                    $join["relatedTable"],
                    $join["localKey"],
                    $join["foreignKey"],
                    $join["type"] ?? "INNER",
                    $conditions,
                    $key,
                );

                // array_merge($conditions, [$join["foreignKey"] => $value])

                JSONResponseHandler::sendResponse($results);
                return;
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

        $model = new Model($table);

        $conditions = self::getConditions();
        $lastInsertId = $model->add($conditions);

        JSONResponseHandler::sendResponse([
            $model->primaryKey => $lastInsertId,
        ]);
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

        $model = new Model($table);

        $conditions = self::getConditions();
        $results = $model->delete($conditions);

        if ($results == false) {
            JSONResponseHandler::throwError(400, "Invalid deletion");
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }
}
