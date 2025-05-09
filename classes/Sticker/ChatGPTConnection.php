<?php

namespace Classes\Sticker;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class ChatGPTConnection
{

    private $oldChats;
    private $idSticker;

    function __construct($idSticker)
    {
        $this->idSticker = $idSticker;
        $query = "SELECT * FROM module_sticker_chatgpt WHERE idSticker = :idSticker";
        $this->oldChats = DBAccess::selectQuery($query, ["idSticker" => $idSticker]);
    }

    /**
     * This function sends a request to the chatGPT API and returns the response
     * 
     * @param string $message The message which is passed to chat gpt
     */
    public static function request($message)
    {
        $apiKey = $_ENV["OPENAI_API_KEY"];
        $organisationKey = $_ENV["OPENAI_ORGANISATION_ID"];
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            "Authorization: Bearer {$apiKey}",
            "OpenAI-Organization: $organisationKey",
            "Content-Type: application/json"
        ];

        $messages = [];
        $messages[] = [
            "role" => "user",
            "content" => $message
        ];

        $data = [
            "model" => "gpt-3.5-turbo",
            "messages" => $messages,
            "max_tokens" => 100
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $result = urldecode($result);

        if (curl_errno($curl)) {
            $result = 'Error:' . curl_error($curl);
        }

        curl_close($curl);
        return $result;
    }

    /**
     * This function takes two parameters, $motivType and $textType, which are used to filter an array of old chat data. 
     * The function then returns the count of elements in the filtered array where the "stickerType" property matches 
     * the $motivType parameter and the "textType" property matches the $textType parameter
     */
    public function getChatCount($motivType, $textType)
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
     * https://stackoverflow.com/questions/75780617/using-php-to-access-chatgpt-api
     * 
     * @param string $query The query which is passed to chat gpt
     * @param string $stickerType The type of the sticker: aufkleber, wandtattoo, textil
     * @param string $textType If the text is for a short description or a long description
     * @param string $info Additional info for chat gpt to generate the text
     * @param string $textStyle The kind of text (e.g ironic, funny, sad)
     */
    public function getTextSuggestion($query, $stickerType, $textType, $info, $textStyle)
    {
        $apiKey = $_ENV["OPENAI_API_KEY"];
        $organisationKey = $_ENV["OPENAI_ORGANISATION_ID"];
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = array(
            "Authorization: Bearer {$apiKey}",
            "OpenAI-Organization: $organisationKey",
            "Content-Type: application/json"
        );

        // Define messages
        $messages = array();
        $messages[] = array("role" => "user", "content" => $this->getMessage($query, $stickerType, $textType, $info, $textStyle));

        // Define data
        $data = array();
        $data["model"] = "gpt-3.5-turbo";
        $data["messages"] = $messages;
        $data["max_tokens"] = 100;

        // init curl
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $result = urldecode($result);
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        } else {
            $this->saveResponse($result, $stickerType, $textType, $info, $textStyle);
        }

        curl_close($curl);
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
        $data = json_decode($result, true);
        $text = $data["choices"][0]["message"]["content"];
        $date = date("Y-m-d");

        $query = "INSERT INTO module_sticker_chatgpt (idSticker, creationDate, chatgptResponse, jsonResponse, stickerType, textType, additionalQuery, textStyle) VALUES (:idSticker, :date, :text, :json, :stickerType, :textType, :info, :textStyle);";
        $params = [
            "idSticker" => $this->idSticker,
            "date" => $date,
            "text" => $text,
            "json" => json_encode($result, JSON_UNESCAPED_UNICODE),
            "stickerType" => $stickerType,
            "textType" => $textType,
            "info" => $info,
            "textStyle" => $textStyle,
        ];

        DBAccess::insertQuery($query, $params);
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

        $chatGPTConnection = new ChatGPTConnection($id);
        $text = $chatGPTConnection->getText($type, $text, $current);

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

        $connector = new ChatGPTConnection($id);
        $connector->getTextSuggestion($title, $type, $text, $additionalText, $additionalStyle);
    }
}
