<?php

namespace Upgrade;

class Console {

    static function execute($command) {
        return shell_exec($command);
    }
}
