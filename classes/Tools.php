<?php

class Tools
{

    public static $data = [];

    static function get($key)
    {
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }

        return null;
    }

    static function add($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * puts the data into the output buffer,
     * used to show data in eventsources
     * 
     * @param int $id
     * @param array $data
     */
    static function output(int $id, array $data)
    {
        echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($data) . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
    }
}
