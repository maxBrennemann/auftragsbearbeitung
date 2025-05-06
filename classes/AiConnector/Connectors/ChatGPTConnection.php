<?php

namespace Project\AiConnector\Connectors;

use Project\AiConnector\AiInterface;

class ChatGPTConnection implements AiInterface
{

    public function getTextSuggestions(mixed $data): array
    {
        return [];
    }

    public function getImagesSuggestions(mixed $data): array
    {
        return [];
    }

    public function getJSONSuggestions(mixed $data): array
    {
        return [];
    }
}
