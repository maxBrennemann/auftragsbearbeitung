<?php

namespace Classes\Controller;

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

    public static function getTemplate(string $template, array $params = []): string
    {
        $content = self::buildTemplate($template, $params);
        return $content;
    }

    private static function buildTemplate(string $template, array $params): string
    {
        if (!file_exists("files/views/{$template}View.php")) {
            throw new \Exception("Template not found");
        }

        ob_start();
        insertTemplate("files/views/{$template}View.php", $params);
        $content = ob_get_clean();

        if ($content === false) {
            throw new \Exception("Failed to get template content");
        }

        return $content;
    }
}
