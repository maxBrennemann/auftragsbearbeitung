<?php

/*
* pattern:
* {
*  rowname0 => {
*      status => "preset",
*      value => "presetValue"
*  },
*  rowname1 => {
*      status => "unset,
*      value => 0
*  }
* }
*/
class UpdateSchedule {

    private $tableName;
    private $pattern;

    private $columns;
    private $values;

    function __construct($tableName, $pattern) {
        $this->tableName = $tableName;
        $this->pattern = $pattern;
    }

    public function executeTableUpdate($data) {
        $this->applyPattern($data);

        $query = "INSERT INTO $this->tableName ($this->columns) VALUES ($this->values)";
        DBAccess::insertQuery($query);
        echo "ok";
    }

    private function applyPattern($data) {
        $columns = $values = "";

        foreach ($this->pattern as $key => $value) {
            $columns .= $key . ", ";

            /* checks if value is preset or it is in data array */
            $val = "";
            if ($value['status'] == "preset") {
                $val = $value['value'];
            } else if ($value['status'] == "unset") {
                $val = $data[$value['value']];
            }

            /* checks if value is string or int to insert it correctly and checks if a cast is necessary */
            if (isset($value['type']) && isset($value['cast'])) {
                $val = $this->castValues($value['type'], $value['cast'], $val);
                $values .= "'$val', ";
            } else {
                if (is_string($val)) {
                    $values .= "'$val', ";
                } else if (is_int($val)) {
                    $values .= $val . ", ";
                }
            }

            if (isset($value["default"]) && $val == "") {
                $val = $value["default"];
            }
        }

        $this->columns = substr($columns, 0, -2);
        $this->values = substr($values, 0, -2);
    }

    static function handlePostenDeletion() {
        
    }

    private function castValues($type, $cast, $value) {
        $result = null;
        switch ($type) {
            case "date":
                $from = isset($cast["from"]) ? $cast["from"] : null;
                $to = isset($cast["to"]) ? $cast["to"] : null;
                $result = DateTime::createFromFormat($from, $value)->format($to);
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

?>