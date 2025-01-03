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
            'model'             => $this->model,
            'max_tokens'        => $maxTokens,
            'messages'          => $messages,
            'tools'             => $this->tool ?? null,
            'stream'            => $this->stream,
            'metadata'          => $options['metadata'] ?? null,
            'stop_sequences'    => $options['metadata'] ?? null,
            'temperature'       => $options['temperature'] ?? null,
            'system'            => $options['system'] ?? null,
            'tool_choice'       => $options['tool_choice'] ?? null,
            'top_k'             => $options['top_k'] ?? null,
            'top_p'             => $options['top_p'] ?? null,
        ];

        return $this->sendRequest('messages', $data);
    }

    public function getMessageTokens(array $messages, $options = []) : array
    {
        $data = [
            'model'         => $this->model,
            'messages'      => $messages,
            'tool_choice'   => $options['tool_choice'] ?? null,
            'tools'         => $this->tool ?? null,
            'system'        => $options['system'] ?? null
        ];
        return $this->sendRequest('/messages/count_tokens', $data);
    }

    public function generateBatchMessages(array $batches): array
    {
        return $this->sendBatchRequest('messages/batches', $batches);
           
    }

    public function getBatchMessages(string $token): array
    {

        return $this->sendBatchRequest('messages/batches/' . $token, 'get');
    }

    public function getBatchMessagesResult(string $token): array
    {
        return $this->sendBatchRequest('messages/batches/' . $token . '/results', 'get');
    }

    public function listBatchMessages(array $options = []): array
    {
        // Initialize query parameters array
        $queryParams = [];

        // Add parameters if provided
        if ($options['before_id'] !== null) {
            $queryParams['before_id'] = $options['before_id'];
        }
        if ($options['after_id'] !== null) {
            $queryParams['after_id'] = $options['after_id'];
        }
        if ($options['limit'] !== null) {
            $queryParams['limit'] = $options['limit'];
        }

        // Build the query string from the array
        $queryString = http_build_query($queryParams);

        // Send the request with query parameters appended
        $url = 'messages/batches?' . $queryString;
        return $this->sendBatchRequest($url, [], 'get');
    }

    public function cancelMessageBatch(string $token): array
    {
        return $this->sendBatchRequest('messages/batches/' . $token . '/cancel', 'get');
    }

    public function deleteMessageBatch($token) : array
    {
        return $this->sendBatchRequest('messages/batches/' . $token , 'delete');
    }

    public function getModels(array $options = []): array
    {
        // Initialize query parameters array
        $queryParams = [];

        // Add parameters if provided
        if ($options['before_id'] !== null) {
            $queryParams['before_id'] = $options['before_id'];
        }
        if ($options['after_id'] !== null) {
            $queryParams['after_id'] = $options['after_id'];
        }
        if ($options['limit'] !== null) {
            $queryParams['limit'] = $options['limit'];
        }

        // Build the query string from the array
        $queryString = http_build_query($queryParams);

        // Send the request with query parameters appended
        $url = 'models?' . $queryString;
        return $this->sendBatchRequest($url, [], 'get');
    }

    public function getModel(string $model):array
    {
        return $this->sendBatchRequest('messages/batches/' . $model , 'get');
    }

}