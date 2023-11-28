<?php

class Tools {

    private static $currentType = "";
    private static $connectionData = [];

    static function get($key) {
        $value = null;

        if (isset(self::$connectionData[$key])) {
            $value = self::$connectionData[$key];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST[$key])) {
               $value = $_POST[$key];
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET[$key])) {
                $value = $_GET[$key];
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            parse_str(file_get_contents("php://input"), $put_vars);
            if (isset($put_vars[$key])) {
                $value = $put_vars[$key];
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            parse_str(file_get_contents("php://input"), $delete_vars);
            if (isset($delete_vars[$key])) {
                $value = $delete_vars[$key];
            }
        }

        if ($key == "type") {
            return self::$currentType;
        }

        return $value;
    }

    static function add($key, $value) {
        self::$connectionData[$key] = $value;
    }

    static function setType($type) {
        self::$currentType = $type;
    }

    /**
     * puts the data into the output buffer,
     * used to show data in eventsources
     * 
     * @param int $id
     * @param array $data
     */
    static function output(int $id, array $data) {
        echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($data) . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
    }

    static function handleUserDisconnected() {}

}
