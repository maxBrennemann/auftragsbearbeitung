<?php

namespace Src\Classes\Project;

class Payments
{
    public static function addPayment(): void {}

    public static function importCSV(): void {}

    public static function removePayment(): void {}

    public static function editPayment(): void {}

    public static function addBankaccount(string $bank, string $name, int $startAmount, string $type): void {}
}
