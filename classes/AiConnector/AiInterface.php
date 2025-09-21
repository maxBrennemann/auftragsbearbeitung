<?php

namespace Classes\AiConnector;

interface AiInterface
{

    /**
     * @param array<string, string> $data
     * @return string
     */
    public function getText(array $data): string;

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function getImage(array $data): array;

    /**
     * @param array<string, string> $data
     * @return array<string, mixed>
     */
    public function getJSON(array $data): array;
}
