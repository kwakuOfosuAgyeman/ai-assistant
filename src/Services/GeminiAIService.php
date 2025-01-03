<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use GuzzleHttp\Client;
use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use Kwakuofosuagyeman\AIAssistant\Traits\GeminiRequest;
use Exception;

class GeminiAIService
{
    use GeminiRequest;

    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultModel;

    public function version(string $version)
    {
        $this->version = $version;
        return $this;
    }

    public function model(string $model)
    {
        $this->defaultModel = $model;
        return $this;
    }

    public function stream()
    {
        $this->stream = true;
        return $this;
    }

    public function codeExecution()
    {
        $this->codeExecution = true;
        return $this;
    }
    

    public function __construct()
    {
        
        $this->apiKey = config('ai.providers.gemini.api_key');
        $this->baseUrl = config('ai.providers.gemini.base_url');
        
        if (empty($this->apiKey) || empty($this->baseUrl)) {
            throw new \InvalidArgumentException("API key and base URL are required in configuration.");
        }
        $this->defaultModel = config('ai.providers.gemini.default_model');
        $this->codeExecution = false;
    }

    public function chat(array $content, array $options = [])
    {
        $modelToUse = $this->defaultModel;
        $endpoint = !$stream ? sprintf('models/%s:generateContent?key=%s', $modelToUse, $this->apiKey) : sprintf('models/%s:streamContent?alt=sse&key=%s', $modelToUse, $this->apiKey);
        $data = [
            
            "contents"                  => $content,
            "safetySettings"            => $options['safetySettings'] ?? null,
            "cachedContent"             => $options['cachedContent'] ?? null,
            "systemInstruction"         => $options['systemInstruction'] ?? null,
            "generationConfig" => [
                "stopSequences"         => $options['stopSequences'] ?? null,
                "temperature"           => $options['temperature'] ?? null,
                "maxOutputTokens"       => $options['maxOutputTokens'] ?? null,
                "topP"                  => $options['topP'] ?? null,
                "topK"                  => $options['topK'] ?? null,
                "response_mime_type"    => $options['response_mime_type'] ?? null,
                "response_schema"       => $options['response_schema'] ?? null,

            ]
        ];
        if ($this->codeExecution === true) {
            $data['tools'] = [['code_execution' => []]];
        }
        if(!empty($options['function_calling'])) {
            $data['tools'] = $options['function_calling']['function'];
            $data['tool_config'] = $options['tool_config'] ?? null;
        }

        return $this->sendRequest($endpoint, $data);
        
    }

    public function generateText(string $prompt, array $options)
    {
        $modelToUse = $this->defaultModel;
        $endpoint = !$stream ? sprintf('models/%s:generateContent?key=%s', $modelToUse, $this->apiKey) : sprintf('models/%s:streamContent?alt=sse&key=%s', $modelToUse, $this->apiKey);
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "cachedContent" => $options['cachedContent'] ?? null,
            "systemInstruction" => $options['systemInstruction'] ?? null,
            "safetySettings" => [
                [
                    "category" => $options['safety_category'] ?? null,
                    "threshold" => $options['safety_threshold'] ?? null,
                ]
            ],
            "generationConfig" => [
                "stopSequences"         => $options['stopSequences'] ?? null,
                "temperature"           => $options['temperature'] ?? null,
                "maxOutputTokens"       => $options['maxOutputTokens'] ?? null,
                "topP"                  => $options['topP'] ?? null,
                "topK"                  => $options['topK'] ?? null,
                "response_mime_type"    => $options['response_mime_type'] ?? null,
                "response_schema"       => $options['response_schema'] ?? null,
            ]
        ];
        if ($this->codeExecution === true) {
            $data['tools'] = [['code_execution' => []]];
        }
        if(!empty($options['function_calling'])) {
            $data['tools'] = $options['function_calling']['function'];
            $data['tool_config'] = $options['tool_config'] ?? null;
        }

        return $this->sendRequest($endpoint, $data);
    }


    public function transcribeAudio(string $audioPath, string $fileName, string $prompt = 'Describe this audio clip', array $options = []): array
    {
        try {
            $modelToUse = $this->defaultModel;
            $file = $this->uploadAudio($audioPath, $fileName);
            $endpoint = sprintf('models/%s:generateContent?key=%s', $modelToUse, $this->apiKey);
            $data = [
                'contents' => 
                [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'file_data' => [
                                    'mime_type' => $this->file['mimeType'],
                                    'file_uri' => $this->file['fileUri'],
                                ],
                            ],
                        ],
                    ],
                ],
                "cachedContent" => $options['cachedContent'] ?? null,
                "systemInstruction" => $options['systemInstruction'] ?? null,
                "safetySettings" => [
                    [
                        "category" => $options['safety_category'] ?? null,
                        "threshold" => $options['safety_threshold'] ?? null,
                    ]
                ],
                "generationConfig" => [
                    "stopSequences"         => $options['stopSequences'] ?? null,
                    "temperature"           => $options['temperature'] ?? null,
                    "maxOutputTokens"       => $options['maxOutputTokens'] ?? null,
                    "topP"                  => $options['topP'] ?? null,
                    "topK"                  => $options['topK'] ?? null,
                    "response_mime_type"    => $options['response_mime_type'] ?? null,
                    "response_schema"       => $options['response_schema'] ?? null,
                ]
            ];
            if(!empty($options['function_calling'])) {
                $data['tools'] = $options['function_calling']['function'];
                $data['tool_config'] = $options['tool_config'] ?? null;
            }
            return $this->sendRequest($endpoint, $data);

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    public function listUploadedfiles()
    {
        $endpoint = sprintf('files?key=%s', $this->apiKey);
        return $this->sendRequest($endpoint, [], 'get');
    }

    public function deleteUploadedfile(string $fileName)
    {
        $endpoint = sprintf('files/%s?key=%s', $fileName, $this->apiKey);
        return $this->sendRequest($endpoint, [], 'delete');
    }

    public function listCaches()
    {
        $endpoint = sprintf('cachedContents?key=%s', $this->apiKey);
        return $this->sendRequest($endpoint, [], 'get');
    }

    public function updateCache(string $cache, string $ttl)
    {
        $data = [
            'ttl' => $ttl
        ];
        $endpoint = sprintf('%s?key=%s',$cache, $this->apiKey);
        return $this->sendRequest($endpoint, $data, 'update');
    }

    public function deleteCache(string $cache)
    {
        $endpoint = sprintf('%s?key=%s', $cache, $this->apiKey);
        return $this->sendRequest($endpoint, [], 'delete');
    }

    public function googleSearch(string $prompt)
    {
        $options['function_calling'] = [
            ["google_search_retrieval" => [
                "dynamic_retrieval_config" => [
                    "mode" => "MODE_DYNAMIC",
                    "dynamic_threshold" => 1,
                ]
            ]]
        ];
        return $this->generateText($prompt, $options);
    }

    public function tuneGoogleModel(array $trainingData, array $hyperparameters, string $base_model, string $displayName)
    {
        $data = [
            "display_name" => $displayName,
            "base_model" => $base_model,
            "tuning_task" => [
                "hyperparameters" => $hyperparameters,
                "training_data" => $trainingData,
            ]
        ];

        $endpoint = sprintf('tunedModels?key=%s', $this->apiKey);
        return $this->sendRequest($endpoint, $data);
    }

    public function checkTunedModelStatus(string $model)
    {
        $endpoint = sprintf('%s?key=%s', $this->apiKey);
        return $this->sendRequest($endpoint, [], 'get');
    }

    public function deleteFineTunedModel(string $model)
    {
        $endpoint = sprintf('%s?key=%s', $this->apiKey);
        return $this->sendRequest($endpoint, [], 'delete');
    }

    public function generateEmbeddings(string $prompt)
    {
        $modelToUse = $this->defaultModel;
        $endpoint = !$stream ? sprintf('models/%s:embedContent?key=%s', $modelToUse, $this->apiKey) : sprintf('models/%s:streamContent?alt=sse&key=%s', $modelToUse, $this->apiKey);
        $data = [
            "model" => $modelToUse,
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "cachedContent" => $options['cachedContent'] ?? null,
            "safetySettings" => [
                [
                    "category" => $options['safety_category'] ?? null,
                    "threshold" => $options['safety_threshold'] ?? null,
                ]
            ],
            "systemInstruction" => $options['systemInstruction'] ?? null,
            "generationConfig" => [
                "stopSequences"         => $options['stopSequences'] ?? null,
                "temperature"           => $options['temperature'] ?? null,
                "maxOutputTokens"       => $options['maxOutputTokens'] ?? null,
                "topP"                  => $options['topP'] ?? null,
                "topK"                  => $options['topK'] ?? null,
                "response_mime_type"    => $options['response_mime_type'] ?? null,
                "response_schema"       => $options['response_schema'] ?? null,
            ]
        ];
        if(!empty($options['function_calling'])) {
            $data['tools'] = $options['function_calling']['function'];
            $data['tool_config'] = $options['tool_config'] ?? null;
        }
    }
}