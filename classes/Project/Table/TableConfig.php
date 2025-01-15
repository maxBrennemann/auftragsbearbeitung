<?php

namespace Classes\Project\Table;

class TableConfig {

    public static function generate() {
        require_once "config/table-config.php";
        $data = getTableConfigFrontOffice();
        $data = json_encode($data, JSON_PRETTY_PRINT);

        echo <<<EOL
            export const tableConfig = $data;
        EOL;
    }

}
