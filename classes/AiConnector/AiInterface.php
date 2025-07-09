<?php

namespace Classes\AiConnector;

interface AiInterface
{
    public function getText(array $data): string;
    public function getImage(array $data): array;
    public function getJSON(array $data): array;
}
