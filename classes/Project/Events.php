<?php

namespace Classes\Project;

set_time_limit(0);

class Events
{
    public static function init(): void
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

    private static function outputHeader(): void
    {
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
    }

    /**
     * @return string[]
     */
    private static function getOutputData(): array
    {
        return [
            "test"
        ];
    }
}
