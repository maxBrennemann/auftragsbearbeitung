<?php

namespace Src\Classes\Controller;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class TemplateController
{
    public static function ajaxGetTemplate(): void
    {
        $template = Tools::get("template");
        $params = Tools::get("params");

        if (is_string($params)) {
            $params = json_decode($params, true);
        }
        if (!is_array($params)) {
            $params = [];
        }

        $content = self::buildTemplate($template, $params);

        JSONResponseHandler::sendResponse([
            "content" => $content,
        ]);
    }

    /**
     * @param string $template
     * @param array<string, mixed> $params
     * @return string
     */
    public static function getTemplate(string $template, array $params = []): string
    {
        $content = self::buildTemplate($template, $params);
        return $content;
    }

    /**
     * @param string $template
     * @param array<string, mixed> $params
     * @throws \Exception
     * @return string
     */
    private static function buildTemplate(string $template, array $params): string
    {
        /* absoluter Pfad: relativ ("../public/views") schlägt fehl, wenn das Arbeitsverzeichnis ein anderes ist,
           z. B. beim Rendern der Fehlerseite aus dem Fehlerhandler */
        $path = ROOT . "public/views/{$template}View.php";
        if (!file_exists($path)) {
            throw new \Exception("Template not found: {$template}");
        }

        ob_start();
        insertTemplate($path, $params);
        $content = ob_get_clean();

        if ($content === false) {
            throw new \Exception("Failed to get template content");
        }

        return $content;
    }
}
