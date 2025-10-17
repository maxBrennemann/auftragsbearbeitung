<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;

class TableGenerator
{

    /**
     * @param array<int, array<string, string>> $data
     * @param array{
     *      styles?:mixed, 
     *      hideOptions?:string[],
     *      hide?:string[],
     *      primaryKey?:string,
     *      link?:string,
     * } $options
     * @param array{
     *      columns:string[],
     *      names?:string[],
     *      primaryKey?:string,
     *      hidden?:string[],
     * } $header
     * 
     * @return string
     */
    public static function create(array $data, array $options, array $header): string
    {
        $theadElements = self::createHeader($header);
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
     * @param array{
     *      columns:string[],
     *      names?:string[],
     *      primaryKey?:string,
     *      hidden?:string[],
     * } $header
     * 
     * @return list<array{key:string, label:string, sortIcon:string}>
     */
    private static function createHeader(array $header): array
    {
        $columns = $header["columns"];
        $names = $header["names"] ?? [];
        $hidden = $header["hidden"] ?? [];

        $elements = [];

        foreach ($columns as $index => $col) {
            if (in_array($col, $hidden)) {
                continue;
            }

            $elements[] = [
                "key" => $col,
                "label" => $names[$index] ?? $col,
                "sortIcon" => "",
            ];
        }
        return $elements;
    }

    /**
     * @param array<int, array<string, string>> $data
     * @param array{
     *      columns:string[],
     *      names?:string[],
     *      primaryKey?:string,
     *      hidden?:string[],
     * } $header
     * @param array{
     *      styles?:mixed, 
     *      hideOptions?:string[],
     *      hide?:string[],
     *      primaryKey?:string,
     *      link?:string,
     * } $options
     * 
     * @return list<list<array{content:string, class:string, primary:string|null}>>
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
                    $primary = $row[$primary] ?? null;
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
     * 
     * @phpstan-ignore-next-line
     */
    private static function createPlaceholderRow(int $count): string
    {
        return '';
    }

    /**
     * @param int $count
     * @param array{
     *      columns:string[],
     *      names:string[],
     *      primaryKey?:string,
     *      hidden?:string[],
     * } $header
     * @param array{
     *      styles?:mixed, 
     *      hideOptions?:string[],
     *      hide?:string[],
     *      primaryKey?:string,
     *      link?:string,
     * } $options
     * @return string
     * 
     * @phpstan-ignore-next-line
     */
    private static function createAddRow(int $count, array $header, array $options): string
    {
        return '';
    }

    /**
     * @param array<int, array<string, string>> $data
     * @param array{
     *      styles?:mixed, 
     *      hideOptions?:string[],
     *      hide?:string[],
     *      primaryKey?:string,
     *      link?:string,
     * } $options
     * @param array{
     *      columns:string[],
     *      names:string[],
     *      primaryKey?:string,
     * } $header
     * @return string
     * 
     * @phpstan-ignore-next-line
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
