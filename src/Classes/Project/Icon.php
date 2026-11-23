<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

/**
 * Lucide-Icons (https://lucide.dev/icons) serverseitig als SVG ausgeben.
 *
 * Icon::get("pencil")                                  16 px, inline
 * Icon::get("trash-2", 16, ["text-danger"], "Löschen") mit Klassen und Titel (für Icon-Buttons)
 *
 * Die Pfade stehen in icons.json, erzeugt von .config/scripts/generate-icons.mjs
 * (läuft bei npm run dev / npm run build). Eigene Icons: public/res/js/icons/README.md.
 */
class Icon
{
    public const DEFAULT_SIZE = 16;

    /**
     * Alte Material-Namen → Lucide, damit übersehene oder gespeicherte Namen weiter funktionieren.
     * @var array<string, string>
     */
    private const LEGACY_NAMES = [
        "iconAdd" => "plus",
        "iconAndroid" => "smartphone",
        "iconApplePhone" => "smartphone",
        "iconArchive" => "archive-restore",
        "iconBell" => "bell",
        "iconChart" => "chart-line",
        "iconCheck" => "check",
        "iconChevronDown" => "chevron-down",
        "iconChevronUp" => "chevron-up",
        "iconDelete" => "trash-2",
        "iconEdit" => "pencil",
        "iconLinux" => "monitor",
        "iconLogout" => "log-out",
        "iconMac" => "monitor",
        "iconMove" => "grip-vertical",
        "iconNewTab" => "external-link",
        "iconOrderAdd" => "clipboard-plus",
        "iconPause" => "pause",
        "iconPersonAdd" => "user-plus",
        "iconPhone" => "smartphone",
        "iconProductAdd" => "tag",
        "iconRemoveConnection" => "unlink",
        "iconSearch" => "search",
        "iconSettings" => "settings",
        "iconTablet" => "tablet",
        "iconUnrecognized" => "circle-question-mark",
        "iconUpload" => "upload",
        "iconWarning" => "triangle-alert",
        "iconWindows" => "monitor",
    ];

    /** @var array<string, list<array{0: string, 1: array<string, string>}>>|null */
    private static ?array $icons = null;

    /**
     * @param string[] $class
     */
    public static function get(string $name, int $size = self::DEFAULT_SIZE, array $class = [], string $title = ""): string
    {
        $name = self::LEGACY_NAMES[$name] ?? $name;
        $node = self::load()[$name] ?? null;
        if ($node === null) {
            return "";
        }

        $inner = "";
        foreach ($node as [$tag, $attributes]) {
            $attr = "";
            foreach ($attributes as $key => $value) {
                $attr .= " " . $key . "=\"" . htmlspecialchars($value, ENT_QUOTES) . "\"";
            }
            $inner .= "<{$tag}{$attr}/>";
        }

        $classes = htmlspecialchars(trim("lucide lucide-{$name} " . implode(" ", $class)), ENT_QUOTES);
        if ($title === "") {
            $a11y = " aria-hidden=\"true\"";
        } else {
            $escapedTitle = htmlspecialchars($title, ENT_QUOTES);
            $a11y = " role=\"img\" aria-label=\"{$escapedTitle}\"";
            $inner = "<title>{$escapedTitle}</title>" . $inner;
        }

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 24 24\""
            . " fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\""
            . " class=\"{$classes}\"{$a11y}>{$inner}</svg>";
    }

    /**
     * Icon in Fließtext-Größe (16 px), z. B. neben einem Label oder in einem Button mit Text.
     */
    public static function getDefault(string $name): string
    {
        return self::get($name, self::DEFAULT_SIZE, ["inline-block", "align-[-0.2em]"]);
    }

    /**
     * @param string[] $class
     */
    public static function getColorized(string $name, int $size, string $color, array $class = [], string $title = ""): string
    {
        $svg = self::get($name, $size, $class, $title);
        if ($svg === "") {
            return "";
        }
        return "<svg style=\"color:" . htmlspecialchars($color, ENT_QUOTES) . "\"" . substr($svg, 4);
    }

    /**
     * @return array<string, list<array{0: string, 1: array<string, string>}>>
     */
    private static function load(): array
    {
        if (self::$icons === null) {
            $json = @file_get_contents(__DIR__ . "/icons.json");
            /** @var array<string, list<array{0: string, 1: array<string, string>}>>|null $data */
            $data = $json === false ? null : json_decode($json, true);
            self::$icons = $data ?? [];
        }
        return self::$icons;
    }

    public static function ajaxIcon(): void
    {
        $name = Tools::get("name");
    }

    public static function ajaxGet(): void
    {
        $name = (string) Tools::get("icon");

        if (Tools::get("custom") != null) {
            $size = (int) Tools::get("width") ?: self::DEFAULT_SIZE;
            $classes = explode(",", (string) Tools::get("classes"));
            $title = (string) (Tools::get("title") ?? "");
            $color = (string) (Tools::get("color") ?? "currentColor");

            $icon = self::getColorized($name, $size, $color, $classes, $title);
        } else {
            $icon = self::getDefault($name);
        }

        if ($icon == "") {
            JSONResponseHandler::returnNotFound("Item not found");
        } else {
            JSONResponseHandler::sendResponse([
                "status" => "success",
                "icon" => $icon,
            ]);
        }
    }
}
