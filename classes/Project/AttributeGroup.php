<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class AttributeGroup
{
    public static function getGroups()
    {
        $groups = DBAccess::selectQuery("SELECT id, attribute_group FROM attribute_group");
        JSONResponseHandler::sendResponse($groups);
    }

    public static function getAttributes()
    {
        $attributeGroupId = (int) Tools::get("id");
        $attributes = DBAccess::selectQuery("SELECT id, value FROM attribute WHERE attribute_group_id = :id", [
            "id" => $attributeGroupId
        ]);
        JSONResponseHandler::sendResponse($attributes);
    }

    public static function addAttributeGroup(): void
    {
        $attribute = Tools::get("name");
        $descr = Tools::get("descr");

        if ($attribute == null || $descr == null) {
            JSONResponseHandler::throwError(400, "Name und Beschreibung müssen ausgefüllt sein");
        }

        $id = DBAccess::insertQuery("INSERT INTO attribute_group (attribute_group, `descr`) VALUES (:name, :descr)", [
            "name" => $attribute,
            "descr" => $descr
        ]);

        JSONResponseHandler::sendResponse(["id" => $id]);
    }

    public static function addAttribute()
    {
        $attributeId = Tools::get("id");
        $value = Tools::get("value");

        if ($attributeId == null || $value == null) {
            JSONResponseHandler::throwError(400, "ID und Wert müssen ausgefüllt sein");
        }

        $currentHighestPosition = DBAccess::selectQuery("SELECT MAX(position) AS max FROM attribute WHERE attribute_group_id = :id", [
            "id" => $attributeId
        ]);
        $currentHighestPosition = $currentHighestPosition[0]["max"] ?? 0;
        $currentHighestPosition++;

        $result = DBAccess::insertQuery("INSERT INTO attribute (attribute_group_id, `value`, position) VALUES (:id, :value, :position)", [
            "id" => $attributeId,
            "value" => $value,
            "position" => $currentHighestPosition
        ]);

        JSONResponseHandler::sendResponse([
            "id" => $result
        ]);
    }

    public static function updatePositions()
    {
        $groupId = (int) Tools::get("id");
        $positions = Tools::get("positions");
        $positions = json_decode($positions, true);

        if ($groupId == null || $positions == null) {
            JSONResponseHandler::throwError(400, "ID und Positionen müssen ausgefüllt sein");
        }

        foreach ($positions as $position) {
            $id = $position["id"];
            $pos = $position["position"];
            DBAccess::updateQuery("UPDATE attribute SET position = :position WHERE id = :id", [
                "position" => $pos,
                "id" => $id
            ]);
        }

        JSONResponseHandler::returnOK();
    }
}
