<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use Exception;
use GuzzleHttp\Client;
use Kwakuofosuagyeman\AIAssistant\Traits\ClaudeRequest;
use Kwakuofosuagyeman\AIAssistant\Traits\FileUploaderTrait;


class ClaudeAIService{
    use ClaudeRequest, FileUploaderTrait;


    protected string $baseUrl;
    protected string $apiKey;
    protected Client $client;
    protected string $version;
    protected string $model;
    protected boolean $stream;
    protected array $tool;


    public function model(string $model)
    {
        $this->model = $model;
        return $this;
    }

    public function stream()
    {
        $this->stream = true;
        return $this;
    }

    public function tool(array $tool)
    {
        $this->tool = $tool;
        return $this;
    }


    public function __construct()
    {
        $this->apiKey = config('ai.providers.claude.api_key');
        $this->baseUrl = config('ai.providers.claude.base_url');
        $this->model = config('ai.providers.claude.model');
        $this->stream = false;

        if (empty($this->apiKey) || empty($this->baseUrl) || empty($this->version)) {
            throw new \InvalidArgumentException("API key, Version and base URL are required in configuration.");
        }
    }

    public function generateText(array $messages, array $options = []): array
    {
        $maxTokens = $options['maxTokens'] ?? config('ai.providers.claude.default_max_tokens');
        $data = [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
            'stream' => $this->stream,
        ];

        return $this->sendRequest('messages', $data);
    }

    /**
     * Analyzes a document and answers a query about it.
     */
    public function analyzeDocumentWithQuery(string $fileUrl, array $options): array
    {
        $query = $options['query'] ?? 'Analyze this document';
        $maxTokens = $options['max_tokens'] ?? 1024;
        $cacheControlMethod = $options['cacheControl'] ?? 'ephemeral';

        try {
            // Step 1: Fetch the file and encode it in Base64
            $fileContent = file_get_contents($fileUrl);
            if ($fileContent === false) {
                throw new Exception("Unable to fetch the file from {$fileUrl}");
            }

            $fileBase64 = base64_encode($fileContent);

            // Step 2: Prepare the JSON payload
            $payload = [
                'model' => $this->model,
                'max_tokens' => $maxTokens,
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
                            ],
                            [
                                'type' => 'text',
                                'text' => $query,
                            ],
                        ],
                    ],
                ],
                'stream' => $this->stream, 
            ];

            // Step 3: Send the API request using the ClaudeRequest trait
            $response = $this->sendRequest('messages', $payload, 'post');

            // Step 4: Return the response or handle errors
            if (isset($response['error'])) {
                throw new Exception($response['error']);
            }

            return $response;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }



    public function useTool(array $messages, array $options): array
    {
        $maxTokens = $options['maxTokens'] ?? 1024;
        if (empty($this->tool) ) {
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
                    'model' => $this->model,
                    'max_tokens' => $maxTokens,
                    'tools' => $this->tool,
                    'messages' => $messages,
                    'stream' => $this->stream,
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