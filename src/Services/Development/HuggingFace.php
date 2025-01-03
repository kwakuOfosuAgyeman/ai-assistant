<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use GuzzleHttp\Client;
use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;

/**
 * In Development
 */
class HuggingFace
{
    protected $client;
    protected $baseUrl;

    private function __construct(array $config)
    {
        $this->client = new Client([
            'headers' => ['Authorization' => "Bearer {$config['api_key']}"]
        ]);
        $this->baseUrl = $config['base_url'];
    }

    private function generateText(string $prompt, array $options = []): array
    {
        $response = $this->client->post("{$this->baseUrl}your-model-name", [
            'json' => ['inputs' => $prompt],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
