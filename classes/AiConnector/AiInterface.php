<?php

namespace Project\AiConnector;

interface AiInterface
{

    public function getText(array $data): array;
    public function getImage(array $data): array;
    public function getJSON(array $data): array;
}
