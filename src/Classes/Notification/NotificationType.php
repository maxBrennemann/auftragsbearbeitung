<?php

namespace Src\Classes\Notification;

final class NotificationType
{
    public const TYPE_STEP = 1;
    public const POSITION = 2;
    public const FILE = 3;
    public const TYPE_NEW_ORDER = 4;
    public const NEW_CUSTOMER = 5;
    public const ORDER_FINISHED = 6;
    public const TYPE_STEP_UPDATE = 7;
    public const ORDER_PAYED = 8;

    /** @var array<int, string> */
    private const NAMES = [
        self::TYPE_STEP => "Schritt",
        self::POSITION => "Position",
        self::FILE => "Datei",
        self::TYPE_NEW_ORDER => "Neuer Auftrag",
        self::NEW_CUSTOMER => "Neuer Kunde",
        self::ORDER_FINISHED => "Auftrag abgeschlossen",
        self::TYPE_STEP_UPDATE => "Aufgaben-Update",
        self::ORDER_PAYED => "Rechnung bezahlt",
    ];

    /** @var array<int, string> Gruppierung für die Filter im Benachrichtigungszentrum. */
    private const CATEGORIES = [
        self::TYPE_STEP => "step",
        self::TYPE_STEP_UPDATE => "step",
        self::POSITION => "order",
        self::TYPE_NEW_ORDER => "order",
        self::ORDER_FINISHED => "order",
        self::NEW_CUSTOMER => "customer",
        self::ORDER_PAYED => "invoice",
    ];

    public static function getName(int $type): string
    {
        return self::NAMES[$type] ?? "Benachrichtigung";
    }

    public static function getCategory(int $type): string
    {
        return self::CATEGORIES[$type] ?? "other";
    }
}
