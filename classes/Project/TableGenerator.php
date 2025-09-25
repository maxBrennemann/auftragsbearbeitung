<?php

namespace Classes\Project;

use Classes\Controller\TemplateController;

class TableGenerator
{

    /**
     * @param array $data
     * @param array{
     *      styles?:array<string, string>, 
     *      hideOptions?:string[],
     *      hide?: string[],
     *      primaryKey: string,
     *      link: string,
     * } $options
     * @return string
     */
    public static function create(array $data, array $options, array $header): string
    {
        $theadElements = self::createHeader($header, $options);
        $tbodyElements = self::getRows($data, $header, $options);

        $tableClassName = $options["styles"]["table"]["className"] ?? [];
        $tableClassName = implode(" ", $tableClassName);

        $theadClassName = $options["styles"]["thead"]["className"] ?? [];
        $theadClassName[] = "cursor-pointer";
        $theadClassName = implode(" ", $theadClassName);
        
        return TemplateController::getTemplate("table", [
            "tableClassName" => $tableClassName,
            "theadElements" => $theadElements,
            "theadClassName" => $theadClassName,
            "tbodyElements" => $tbodyElements,
            "actionColumn" => "",
            "actionElement" => "",
            "tfoot" => "",
            "link" => $options["link"] ?? null,
            "primaryKey" => $options["primaryKey"] ?? null,
        ]);
    }

    /**
     * @param array $header
     * @param array $options
     * @return array
     */
    private static function createHeader(array $header, array $options): array
    {
        $columns = $header["columns"];
        $names = $header["names"];
        $hidden = $header["hidden"] ?? [];

        $elements = [];

        foreach ($columns as $index => $col) {
            if (in_array($col, $hidden)) {
                continue;
            }

            $elements[] = [
                "key" => $col,
                "label" => $names[$index],
                "sortIcon" => "",
            ];
        }
        return $elements;
    }

    /**
     * @param array $data
     * @param array $header
     * @param array $options
     * @return array
     */
    private static function getRows(array $data, array $header, array $options): array
    {
        $columns = $header["columns"];
        $hidden = $header["hidden"] ?? [];

        $elements = [];

        foreach ($data as $row) {
            $parsedRow = [];
            foreach ($columns as $columnName) {
                if (in_array($columnName, $hidden)) {
                    continue;
                }

                $class = $options["styles"]["key"][$columnName] ?? [];
                $class[] = "cursor-pointer";

                $primary = $options["primaryKey"] ?? null;
                if ($primary) {
                    $primary = $row[$options["primaryKey"]] ?? null;
                }

                $parsedRow[] = [
                    "content" => $row[$columnName],
                    "class" => implode(" ", $class),
                    "primary" => $primary,
                ];
            }
            $elements[] = $parsedRow;
        }

        return $elements;
    }

    /**
     * @param int $count
     * @return string
     */
    private static function createPlaceholderRow(int $count): string
    {
        return '';
    }

    /**
     * @param int $count
     * @param array $header
     * @param array $options
     * @return string
     */
    private static function createAddRow(int $count, array $header, array $options): string
    {
        return '';
    }

    /**
     * @param array $data
     * @param array $options
     * @param array $header
     * @return string
     */
    private static function createSumRow(array $data, array $options, array $header): string
    {
        return '';
    }

    public static function getSorterIcon(): string
    {
        return "";
    }
}
