<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use Exception;
use GuzzleHttp\Client;


class ClaudeAIService{
    protected string $baseUrl;
    protected string $apiKey;
    protected Client $client;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.claude.api_key');
        $this->baseUrl = config('ai.providers.claude.base_url');
        $this->version = config('ai.providers.claude.version');

        if (empty($this->apiKey) || empty($this->baseUrl) || empty($this->version)) {
            throw new \InvalidArgumentException("API key, Version and base URL are required in configuration.");
        }

        $this->client = new Client([
            'base_url' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function generateText(array $messages, array $options = []): array
    {
        $maxTokens = $option['maxTokens'] ?? config('api.providers.claude.default_max_tokens');
        $model = $option['model'] ?? config('api.providers.claude.model');
        $stream = $option['stream'] ?? false;
        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
                'json' => [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'messages' => $messages,
                    'stream' => $stream,
                ],
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
    public function analyzeDocumentWithQuery(string $fileUrl, array $options): array
    {
        $query = $option['query'] ?? 'Analyze this document';
        $model =  $option['model'] ?? config('ai.providers.claude.model') ;
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
                    'anthropic-version' => $this->version ,
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

    public function useTool(array $messages, array $options): array
    {
        $maxTokens = $options['maxTokens'] ?? 1024;
        $model = $options['model'] ?? config('ai.providers.claude.model');
        $stream = $options['stream'] ?? false;

        $tool = $options['tool']; 
        if (empty($tool) ) {
            throw new \InvalidArgumentException("Tool is a required option in configuration.");
        }

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
                'json' => [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'tools' => $tool,
                    'messages' => $messages,
                    'stream' => $stream,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function generateBatchMessages(array $batches, array $options): array
    {
        $maxTokens = $option['maxTokens'] ?? config('api.providers.claude.default_max_tokens');
        $stream = $option['stream'] ?? false;
        try {
            $response = $this->client->post(config('api.providers.claude.batch_url'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
                'requests' => $batches,
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

    public function getBatchMessages(string $token): array
    {
        try {
            $response = $this->client->get(config('api.providers.claude.batch_url') . $token, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
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

    public function getBatchMessagesResult(string $token): array
    {
        try {
            $response = $this->client->get(config('api.providers.claude.batch_url') . $token . '/results', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
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

    public function listBatchMessages(): array
    {
        try {
            $response = $this->client->get(config('api.providers.claude.batch_url'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
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

    public function cancelMessageBatch(string $token): array
    {
        try {
            $response = $this->client->get(config('api.providers.claude.batch_url') . $token . '/cancel', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                ],
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

}