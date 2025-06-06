<?php

namespace Classes\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\AiConnector\Connectors\ChatGPTConnection;

class TextModification
{

    private $oldChats;
    private $idSticker;

    public function __construct($idSticker)
    {
        $this->idSticker = $idSticker;
        $this->oldChats = DBAccess::selectQuery("SELECT * FROM module_sticker_chatgpt WHERE idSticker = :idSticker", [
            "idSticker" => $idSticker
        ]);
    }

    /**
     * This function takes two parameters, $motivType and $textType, 
     * which are used to filter an array of old chat data. 
     * The function then returns the count of elements in the filtered array 
     * where the "stickerType" property matches the $motivType parameter 
     * and the "textType" property matches the $textType parameter
     */
    public function getChatCount($motivType, $textType): int
    {
        return count(array_filter(
            $this->oldChats,
            fn($element) => $element["stickerType"] == $motivType && $element["textType"] == $textType
        ));
    }

    public function getText($motivType, $textType, $index)
    {
        $filteredChats = array_filter(
            $this->oldChats,
            fn($element) => $element["stickerType"] == $motivType && $element["textType"] == $textType
        );
        if (isset($filteredChats[$index])) {
            return $filteredChats[$index]["chatgptResponse"];
        }
        return false;
    }

    /**
     * @param string $query The query which is passed to chat gpt
     * @param string $stickerType The type of the sticker: aufkleber, wandtattoo, textil
     * @param string $textType If the text is for a short description or a long description
     * @param string $info Additional info for chat gpt to generate the text
     * @param string $textStyle The kind of text (e.g ironic, funny, sad)
     */
    public function getTextSuggestion($query, $stickerType, $textType, $info, $textStyle): array
    {
        $query = $this->getMessage($query, $stickerType, $textType, $info, $textStyle);
        $chatGPTConnection = new ChatGPTConnection();
        $result = $chatGPTConnection->getText([
            "input" => $query,
        ]);
        return $this->saveResponse($result, $stickerType, $textType, $info, $textStyle);
    }

    private function getMessage($query, $stickerType, $textType, $info, $textStyle)
    {
        $length = $textType == "long" ? "50" : "20";

        $contentType = "";
        switch ($stickerType) {
            case "aufkleber":
                $contentType = "den Aufkleber";
                break;
            case "wandtattoo":
                $contentType = "das Wandtattoo";
                break;
            case "textil":
                $contentType = "das Textil (Shirt)";
                break;
        }

        $queryText = "Hallo, bitte erstelle mir einen Werbetext über $contentType $query, der ca. $length Wörter lang sein soll. Der Text soll in diesem Stil verfasst werden: $textStyle. Verwende bitte folgende Zusatzinfos: $info. Vielen Dank!";
        return $queryText;
    }

    private function saveResponse($result, $stickerType, $textType, $info, $textStyle)
    {
        $date = date("Y-m-d");

        $query = "INSERT INTO module_sticker_chatgpt (idSticker, creationDate, chatgptResponse, jsonResponse, stickerType, textType, additionalQuery, textStyle) VALUES (:idSticker, :date, :text, :json, :stickerType, :textType, :info, :textStyle);";
        $params = [
            "idSticker" => $this->idSticker,
            "date" => $date,
            "text" => $result,
            "json" => "",
            "stickerType" => $stickerType,
            "textType" => $textType,
            "info" => $info,
            "textStyle" => $textStyle,
        ];

        $id = DBAccess::insertQuery($query, $params);
        return [
            "text" => $result,
            "textId" => $id,
        ];
    }

    public static function iterateText()
    {
        $id = (int) Tools::get("id");
        $type = Tools::get("type");
        $text = Tools::get("form");

        $direction = Tools::get("direction");
        $current = (int) Tools::get("current");
        /* adapting to array index */
        $current--;

        if ($direction == "next") {
            $current++;
        } else if ($direction == "back") {
            $current--;
        }

        if ($current < 0) {
            $current = 0;
        }

        $textModification = new TextModification($id);
        $text = $textModification->getText($type, $text, $current);

        $status = "success";
        if ($text == false) {
            $status = "error";
        }

        JSONResponseHandler::sendResponse([
            "status" => $status,
            "text" => $text,
            "current" => $current,
        ]);
    }

    public static function newText()
    {
        $id = (int) Tools::get("id");
        $type = Tools::get("type");
        $text = Tools::get("form");

        $title = Tools::get("title");
        $additionalText = Tools::get("additionalText");
        $additionalStyle = Tools::get("additionalStyle");

        $connector = new TextModification($id);
        $response = $connector->getTextSuggestion($title, $type, $text, $additionalText, $additionalStyle);

        JSONResponseHandler::sendResponse($response);
    }

    public static function getTextGenerationTemplate()
    {
        $stickerId = Tools::get("id");
        $stickerType =Tools::get("type");
        $textType = Tools::get("text");

        $query = "SELECT id, chatgptResponse, DATE_FORMAT(creationDate, '%d.%m.%Y') AS creationDate, textType, additionalQuery, textStyle 
            FROM module_sticker_chatgpt 
            WHERE idSticker = :stickerId 
                AND stickerType = :stickerType
                AND textType = :textType;";
        $result = DBAccess::selectQuery($query, [
            "stickerId" => $stickerId,
            "stickerType" => $stickerType,
            "textType" => $textType,
        ]);

        $content = \Classes\Controller\TemplateController::getTemplate("sticker/textModification", [
            "texts" => $result,
        ]);
        
        JSONResponseHandler::sendResponse([
            "template" => $content,
        ]);
    }
}
