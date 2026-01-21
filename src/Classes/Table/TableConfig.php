<?php

namespace Src\Classes\Table;

class TableConfig
{
    public static function generate(): void
    {
        require_once ROOT . "src/table-config.php";
        $data = getTableConfigFrontOffice();
        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        echo <<<EOL
        // AUTO-GENERATED FILE
        // Do not edit manually.
        // Generated via: php ./console autoUpgrade --skip-migration
        export const tableConfig = $data;
        
        EOL;
    }

}
