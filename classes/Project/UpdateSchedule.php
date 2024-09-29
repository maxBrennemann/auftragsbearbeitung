<?php

namespace Classes\Project;

use Classes\DBAccess;

class UpdateSchedule
{

    private $tableName;
    private $pattern;

    private $columns;
    private $values;
    private $parameters;

    function __construct($tableName, $pattern)
    {
        $this->tableName = $tableName;
        $this->pattern = $pattern;
    }

    public function executeTableUpdate($data)
    {
        $this->applyPattern($data);

        $query = "INSERT INTO $this->tableName ($this->columns) VALUES ($this->parameters)";

        var_dump($query);
        var_dump($this->values);

        DBAccess::insertQuery($query, $this->values);
        echo "ok";
    }

    private function applyPattern($data)
    {
        $columns = array();
        $values = array();
        $parameters = array();
        $count = 0;

        foreach ($this->pattern as $key => $value) {
            $columns[] = $key;

            /* checks if value is preset or it is in data array */
            $insertValue = "";
            switch ($value["status"]) {
                case "preset":
                    $insertValue = $value['value'];
                    break;
                case "unset":
                    $insertValue = $data[$value['value']];
                    break;
            }

            /* checks if value is string or int to insert it correctly and checks if a typecast is necessary */
            if (isset($value['type']) && isset($value['cast'])) {
                $insertValue = $this->castValues($value['type'], $value['cast'], $insertValue);
            }

            if (isset($value["default"]) && $insertValue == "") {
                $insertValue = $value["default"];
            }

            $values[":param$count"] = $insertValue;
            $parameters[] = ":param$count";
            $count++;
        }

        $this->columns = implode(", ", $columns);
        $this->parameters = implode(", ", $parameters);
        $this->values = $values;
    }

    static function handlePostenDeletion() {}

    private function castValues($type, $cast, $value)
    {
        $result = null;
        switch ($type) {
            case "date":
                $from = isset($cast["from"]) ? $cast["from"] : null;
                $to = isset($cast["to"]) ? $cast["to"] : null;
                $result = \DateTime::createFromFormat($from, $value)->format($to);
                break;
            case "float":
                $separator = isset($cast["separator"]) ? $cast["separator"] : null;
                $value = str_replace($separator, ".", $value);
                $value = (float) $value;
                $value = 100 * $value;
                $value = (int) $value;

                $result = $value;
                break;
            case "cm":
                $value = explode(',', $value);
                if (sizeof($value) == 2) {
                    $value = ((int) $value[0]) + ((int) $value[1]);
                } else {
                    $value = ((int) $value[0]) * 10;
                }

                $result = $value;
                break;
        }

        return $result;
    }
}
