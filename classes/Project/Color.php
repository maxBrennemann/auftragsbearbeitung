<?php

namespace Classes\Project;

use Classes\Controller\TemplateController;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Color
{
    private string $colorName = "";
    private string $hexValue = "";
    private string $shortName = "";
    private string $producer = "";


    public function __construct(string $colorName, string $hexValue, string $shortName, string $producer)
    {
        $this->colorName = $colorName;
        $this->hexValue = $hexValue;
        $this->shortName = $shortName;
        $this->producer = $producer;
    }

    public function save()
    {
        if (!$this->checkHex()) {
            throw new \Exception("wrong hex code");
        }

        $query = "INSERT INTO color (color_name, hex_value, short_name, producer) VALUES (:colorName, :hexValue, :shortName, :producer);";

        $id = DBAccess::insertQuery($query, [
            "colorName" => $this->colorName,
            "hexValue" => $this->hexValue,
            "shortName" => $this->shortName,
            "producer" => $this->producer,
        ]);

        return $id;
    }

    private function checkHex()
    {
        $pregMatch = preg_match('/^[0-9a-fA-F]{6}$/', $this->hexValue);

        if ($pregMatch == 1) {
            return true;
        }

        return false;
    }

    public static function get()
    {
        return DBAccess::selectAll("color");
    }

    public static function convertHexToHTML($data)
    {
        foreach ($data["results"] as $key => $value) {
            $data["results"][$key]["hex_value"] = "<div class=\"farbe\" style=\"background-color: #" . $value["hex_value"] . "\"></div>";
        }
    }

    public static function convertHex($data)
    {
        foreach ($data as $key => $value) {
            $data[$key]["hex_value"] = "<div class=\"farbe\" style=\"background-color: #" . $value["hex_value"] . "\"></div>";
        }
        return $data;
    }

    public static function renderColorTemplate()
    {
        $colors = Color::get();
        $template = TemplateController::getTemplate("color", [
            "colors" => $colors,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $template,
        ]);
    }
}
