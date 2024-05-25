<?php

class JSONResponseHandler
{

    public static function throwError(int $httpStatusCode, String|array $message)
    {
        http_response_code($httpStatusCode);
        echo json_encode(array("message" => $message));
        die();
    }

    public static function sendResponse($data)
    {
        http_response_code(200);
        echo json_encode($data);
    }

    public static function returnOK()
    {
        http_response_code(200);
        echo json_encode(array("message" => "OK"));
    }

    public static function returnNotFound()
    {
        http_response_code(404);
        echo json_encode(array("message" => "Not found"));
    }
}
