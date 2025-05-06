<?php

namespace Project\AiConnector\Connectors;

use GuzzleHttp\Client;

use Project\AiConnector\AiInterface;

class ChatGPTConnection implements AiInterface
{

    /* chatgpt api options */
    private string $model = "gpt-4o-mini";
    private float $temperature = 1;
    private int $maxOutputTokens = 1028;
    private bool $store = true;
    private float $topP = 1;

    /* processed data */
    private string $input = "";
    private string $format = "text";
    private array $text = [];
    private string $name = "json_structure";

    public function __construct(
        string $model = "gpt-4o-mini",
        float $temperature = 1,
        int $maxOutputTokens = 1028,
        bool $store = true,
        float $topP = 1
    ) {
        $this->model = $model;
        $this->temperature = $temperature;
        $this->maxOutputTokens = $maxOutputTokens;
        $this->store = $store;
        $this->topP = $topP;
    }

    private function request(): array
    {
        $client = new Client();
        $response = $client->post("https://api.openai.com/v1/responses", [
            "headers" => [
                "Content-Type"  => "application/json",
                "Authorization" => "Bearer " . $_ENV["OPENAI_API_KEY"],
            ],
            "json" => [
                "model" => $this->model,
                "input" => $this->input,
                "text" => [
                    "format" => [
                        "type" => $this->format,
                        "name" => $this->name,
                        $this->text,
                    ]
                ],
                "tools" => [],
                "temperature" => $this->temperature,
                "max_output_tokens" => $this->maxOutputTokens,
                "top_p" => $this->topP,
                "store" => $this->store,
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        $text = $body["output"][0]["content"][0]["text"];
        return json_decode($text, true);
    }

    public function getText(array $data): array
    {
        return [];
    }

    public function getImage(array $data): array
    {
        return [];
    }

    public function getJSON(array $data): array
    {
        $this->input = $data["input"];
        $this->format = $data["format"];

        $structure = $data["structure"];
        $required = $data["required"];
        $additionalProperties = $data["additionalProperties"];

        $this->text = [
            "strict" => true,
            "schema" => [
                "type" => "object",
                "properties" => [
                    $structure,
                ],
                "required" => $required,
                "additionalProperties" => $additionalProperties,
            ],
        ];

        return $this->request();
    }
}
