<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use Exception;
use GuzzleHttp\Client;


class ClaudeAIService implements AIService {
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.claude.api_key');
        $this->baseUrl = config('ai.providers.claude.base_url');

        if (empty($this->apiKey) || empty($this->baseUrl)) {
            throw new \InvalidArgumentException("API key and base URL are required in configuration.");
        }

        $this->client = new Client([
            'base_url' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function generateText(string $prompt, array $options = []): array
    {
        try {
            $payload = array_merge(['inputs' => $prompt], $options);

            $response = $this->client->post($this->baseUrl, [
                'json' => $payload,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            // Ensure proper response structure
            if (!is_array($responseBody)) {
                throw new Exception("Unexpected response format from Claude AI service.");
            }

            return $responseBody;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Analyzes a document and answers a query about it.
     */
    public function analyzeDocumentWithQuery(string $fileUrl, array $options = []): array
    {
        $query = $option['query'] ?? 'Analyze this document';
        $model =  $option['model'] ?? config('ai.providers.claude.model') ;
        $version = $option['version'] ?? config('ap.providers.claude.version'); 
        $max_tokens = $option['max_tokens'] ?? 1024;
        try {
            // Step 1: Fetch the file and encode it in Base64
            $fileContent = file_get_contents($fileUrl);
            if ($fileContent === false) {
                throw new Exception("Unable to fetch the file from {$fileUrl}");
            }

            $fileBase64 = base64_encode($fileContent);

            // Step 2: Prepare the JSON payload
            $payload = [
                'model' => $model ,
                'max_tokens' => $max_tokens,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'document',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => 'application/pdf',
                                    'data' => $fileBase64,
                                ],
                                'cache_control' => [
                                    "type" => "ephemeral"
                                ]
                            ],
                            [
                                'type' => 'text',
                                'text' => $query,
                            ],
                        ],
                    ],
                ],
                
            ];

            // Step 3: Send the API request
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'content-type' => 'application/json',
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => $version ,
                ],
                'json' => $payload,
            ]);

            // Step 4: Parse and return the response
            $responseBody = json_decode($response->getBody()->getContents(), true);

            return $responseBody;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}