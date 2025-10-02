<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

require_once "src/table-config.php";

class Test
{
    public static function migrateFarbenToColor(): void
    {
        $query = "SELECT f.Farbe, f.Farbwert, f.Notiz, f.Hersteller, fa.id_auftrag FROM farben f LEFT JOIN farben_auftrag fa ON f.Auftragsnummer = fa.id_auftrag;";
        $data = DBAccess::selectQuery($query);

        foreach ($data as $row) {
            $query = "INSERT INTO color (color_name, hex_value, short_name, producer) VALUES (:c, :h, :s, :p);";
            $id = DBAccess::insertQuery($query, [
                "c" => $row["Farbe"],
                "h" => $row["Farbwert"],
                "s" => $row["Notiz"],
                "p" => $row["Hersteller"],
            ]);

            if ($row["id_auftrag"] != null) {
                $query = "INSERT INTO color_auftrag (id_color, id_auftrag) VALUES (:i, :a);";
                DBAccess::insertQuery($query, [
                    "i" => $id,
                    "a" => $row["id_auftrag"],
                ]);
            }
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function table(): void
    {
        $data = DBAccess::selectQuery("SELECT * FROM address LIMIT 2;");
        
        $header = getTableConfig()["address"];
        $options = [
            "hideOptions" => ["all"],
        ];
        $table = TableGenerator::create($data, $options, $header);
        echo $table;
    }
}
