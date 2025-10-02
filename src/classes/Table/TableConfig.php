<?php

namespace Src\Classes\Table;

class TableConfig
{
    public static function generate(): void
    {
        require_once "src/table-config.php";
        $data = getTableConfigFrontOffice();
        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        echo <<<EOL
            export const tableConfig = $data;
        EOL;
    }

}
