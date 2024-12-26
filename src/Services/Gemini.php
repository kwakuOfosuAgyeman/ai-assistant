<?php

namespace YourVendor\AIAssistant\Services;

use GuzzleHttp\Client;
use YourVendor\AIAssistant\Contracts\AIService;
use Exception;

class GeminiService implements AIService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        
        $this->apiKey = config('ai.providers.gemini.api_key');
        $this->baseUrl = config('ai.providers.gemini.base_url');
        if (empty($this->apiKey) || empty($this->baseUrl)) {
            throw new \InvalidArgumentException("API key and base URL are required in configuration.");
        }
        $this->model = config('ai.providers.gemini.default_model') ?? $model;
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
        $modelToUse = $option['model'] ?? $this->defaultModel;
        $endpoint = sprintf('%s/models/%s:generateContent?key=%s', $this->baseUrl, $modelToUse, $this->apiKey);

        try {
            $response = $this->client->post(
                $endpoint,
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ],
                ]
            );

            return [
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }


    public function transcribeAudio(string $audioPath, array $options = []): array
    {
        $modelToUse = $option['model'] ?? $this->defaultModel;
        $mimeType = mime_content_type($audioPath);
        $fileSize = filesize($audioPath);
        $displayName = 'AUDIO';

        try {
            // Step 1: Start resumable upload
            $initialResponse = $this->client->post(
                sprintf('%s/upload/v1beta/files?key=%s', $this->baseUrl, $this->apiKey),
                [
                    'headers' => [
                        'X-Goog-Upload-Protocol' => 'resumable',
                        'X-Goog-Upload-Command' => 'start',
                        'X-Goog-Upload-Header-Content-Length' => $fileSize,
                        'X-Goog-Upload-Header-Content-Type' => $mimeType,
                    ],
                    'json' => [
                        'file' => [
                            'display_name' => $displayName,
                        ],
                    ],
                ]
            );

            $uploadUrl = $initialResponse->getHeader('X-Goog-Upload-URL')[0];

            // Step 2: Upload audio file
            $this->client->post(
                $uploadUrl,
                [
                    'headers' => [
                        'Content-Length' => $fileSize,
                        'X-Goog-Upload-Offset' => 0,
                        'X-Goog-Upload-Command' => 'upload, finalize',
                    ],
                    'body' => fopen($audioPath, 'rb'),
                ]
            );

            // Step 3: Transcribe using uploaded file
            $fileUri = $uploadUrl; // Assuming the file URI is derived from the upload URL
            $endpoint = sprintf('%s/models/%s:generateContent?key=%s', $this->baseUrl, $modelToUse, $this->apiKey);

            $response = $this->client->post(
                $endpoint,
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => 'Describe this audio clip'],
                                    [
                                        'file_data' => [
                                            'mime_type' => $mimeType,
                                            'file_uri' => $fileUri,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );

            return [
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}