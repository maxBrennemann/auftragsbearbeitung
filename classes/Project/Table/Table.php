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

        $hooks = $tableConfig["hooks"] ?? [];
        $model = new Model($hooks);
        $model->tableName = $table;
        $model->fillable = [];

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

    public static function createData() {}

    public static function updateData() {}

    public static function deleteData() {
        $table = Tools::get("tablename");
        $config = self::getTableConfig();
        $tableConfig = $config[$table] ?? null;

        if (!$tableConfig) {
            JSONResponseHandler::returnNotFound([
                "error" => "Invalid table namne",
            ]);
        }

        $model = new Model($tableConfig["hooks"]);
        $model->tableName = $table;
        $model->fillable = [];

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
