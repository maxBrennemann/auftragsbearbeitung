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

            /* checks if value is string or int to insert it correctly */
            if (is_string($val)) {
                $values .= "'$val', ";
            } else if (is_int($val)) {
                $values .= $val . ", ";
            }
        }

        $this->columns =  substr($columns, 0, -2);
        $this->values =  substr($values, 0, -2);
    }

    static function handlePostenDeletion() {
        
    }

}

?>