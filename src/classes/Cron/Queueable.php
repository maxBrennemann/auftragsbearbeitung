<?php

namespace Src\Classes\Cron;

interface Queueable
{
    public static function handle(): void;
}
