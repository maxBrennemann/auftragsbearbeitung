<?php

namespace Classes\Project;

set_time_limit(0);

class Events
{

    public static function init()
    {
        self::outputHeader();
        $start = time();
        while (time() - $start < 30) {
            $newData = self::getOutputData();

            if ($newData) {
                echo "event: message\n";
                echo "data: " . json_encode($newData) . "\n\n";
                flush();
            }

            sleep(1);
        }
    }

    private static function outputHeader()
    {
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
    }

    private static function getOutputData()
    {
        return [
            "test"
        ];
    }
}
