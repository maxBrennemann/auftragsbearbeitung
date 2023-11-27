<?php

class JSONResponseHandler {

    public static function throwError(int $httpStatusCode, String $message) {
        http_response_code($httpStatusCode);
        echo json_encode(array("message" => $message));
        die();
    }

}
