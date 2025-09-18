<?php

namespace Classes\Pdf;

use MaxBrennemann\PhpUtilities\DBAccess;

class PDFTexts
{

    /**
     * @param string $type
     * @return array<int, string>
     */
    public static function get(string $type): array
    {
        $query = "SELECT `text` FROM pdf_texts WHERE `type` = :type AND `status` = 'active';";
        $data = DBAccess::selectQuery($query, ["type" => $type]);

        $result = [];
        foreach ($data as $row) {
            $result[] = $row["text"];
        }

        return $result;
    }
}