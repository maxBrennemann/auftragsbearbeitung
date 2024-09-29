<?php

namespace Classes\Cron;

interface Queueable
{

    public static function handle();
}
