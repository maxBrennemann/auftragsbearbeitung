<?php

namespace Project\AiConnector;

interface AiInterface
{

    public function getTextSuggestions(mixed $data): array;
    public function getImagesSuggestions(mixed $data): array;
    public function getJSONSuggestions(mixed $data): array;
}
