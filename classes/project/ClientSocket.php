<?php

class ClientSocket {

    private static $socket;

    private static function connectSocket() {
        if (self::$socket == NULL) {
            self::$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!is_resource(self::$socket)) onSocketFailure("Failed to create socket");
            socket_connect(self::$socket, "localhost", 29180);
        }
    }

    public static function writeMessage($message, $insertion) {
        self::connectSocket();
        socket_write(self::$socket, "$message\r\n");

        $data = "";

        $errorcode = socket_last_error();
        if ($insertion == true) {

        } else {
            socket_set_nonblock(self::$socket);
            /*while(true) {
                $line = socket_read(self::$socket, 1024, PHP_NORMAL_READ);
                $data .= $line;
                echo $line . "<br>";
                if ($line == "resultEnd") break;

                if (strpos($data, 'resultEnd') !== false) {
                    break;
                }
            }*/
            $line = @socket_read(self::$socket, 1024);
            echo $line;
            $_SESSION['searchResult'] = $line;
            $data = $line;

            if (@socket_recv(self::$socket, $buf, 2045, MSG_WAITALL) === FALSE) {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                echo "socket msg: " . @socket_read(self::$socket, 2045);
                echo "error msg: ". $errormsg;
            }

            //print the received message
            echo "test: " .$buf;
        }

        return $data;
    }
}
?>