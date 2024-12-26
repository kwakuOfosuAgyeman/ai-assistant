<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;


class ClaudeAIService implements AIService {
    protected string $baseUrl;
    protected LoggerInterface $logger;

    public function __construct( array $config, LoggerInterface $logger)
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
        $this->logger = $logger;
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
            $this->logger->error('ClaudeAIService: Error generating text', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'options' => $options,
            ]);

            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Analyzes a document and answers a query about it.
     */
    public function analyzeDocumentWithQuery(string $fileUrl, string $query, string $model = '', string $version = ''): array
    {
        try {
            // Step 1: Fetch the file and encode it in Base64
            $fileContent = file_get_contents($fileUrl);
            if ($fileContent === false) {
                throw new Exception("Unable to fetch the file from {$fileUrl}");
            }

            $fileBase64 = base64_encode($fileContent);

            // Step 2: Prepare the JSON payload
            $payload = [
                'model' => $model ?? config('ai.providers.claude.model'),
                'max_tokens' => 1024,
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
                    'x-api-key' => config('api.providers.claude.api_key'),
                    'anthropic-version' => $version ?? config('ap.providers.claude.version'),
                ],
                'json' => $payload,
            ]);

            // Step 4: Parse and return the response
            $responseBody = json_decode($response->getBody()->getContents(), true);

            $this->logger->info('ClaudeAIService: Document analysis successful.', [
                'fileUrl' => $fileUrl,
                'query' => $query,
                'response' => $responseBody,
            ]);

            return $responseBody;
        } catch (Exception $e) {
            $this->logger->error('ClaudeAIService: Error analyzing document', [
                'error' => $e->getMessage(),
                'fileUrl' => $fileUrl,
                'query' => $query,
            ]);

            return ['error' => 'An error occurred while processing the document analysis.'];
        }
    }
}