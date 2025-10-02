<?php

namespace Src\Classes\AiConnector\Connectors;

use Src\Classes\AiConnector\AiInterface;
use GuzzleHttp\Client;

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

    /** @var array<string, mixed> */
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

    private function request(): string
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
                    "format" => $this->getForamt(),
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
        return $text;
    }

    /**
     * @return array<mixed, mixed>
     */
    private function getForamt(): array
    {
        if ($this->format == "text") {
            return [
                "type" => $this->format,
            ];
        } elseif ($this->format == "json_schema") {
            return [
                "type" => $this->format,
                "name" => $this->name,
                $this->text,
            ];
        }

        return [];
    }

    /**
     * @param array<string, string> $data
     * @return string
     */
    public function getText(array $data): string
    {
        $this->input = $data["input"];
        $this->format = "text";
        $this->name = "text_output";
        $this->text = [];

        return $this->request();
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function getImage(array $data): array
    {
        return [];
    }

    /**
     * @param array<string, string> $data
     * @return array<string, mixed>
     */
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

        $data = $this->request();
        $data = json_decode($data, true);
        return $data;
    }
}
