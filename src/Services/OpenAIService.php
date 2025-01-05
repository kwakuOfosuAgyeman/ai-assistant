<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use OpenAI;
use Exception;

class OpenAIService
{
    protected $client;
    protected string $apiKey;
    protected string $model;
    protected bool $stream;
    protected array $tool;

    public function model(string $model)
    {
        $this->model = $model;
        return $this;
    }

    public function tool(array $tool)
    {
        $this->tool = $tool;
        return $this;
    }

    public function stream()
    {
        $this->stream = true;
        return $this;
    }

    public function __construct()
    {
        $this->apiKey = config('ai.providers.openai.api_key');
        $this->model = config('ai.provider.openai.default_model');
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException("API key is required in configuration.");
        }
        $this->client = OpenAI::client($this->apiKey);
        $this->stream = false;
    }

    /**
     * Generate text based on a given prompt.
     */
    public function generateText(string $prompt, array $options = []): array
    {
        try {
            if($this->stream){
                $response = $this->client->completions()->createStreamed([
                    'prompt' => $prompt,
                    'model' => $this->model,
                    'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                    'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
                    'functions' => $options['functions'] ?? null,
                    'stream_options' => [
                        'include_usage' => true,
                    ]
                ]);
            }else{
                $response = $this->client->completions()->create([
                    'prompt' => $prompt,
                    'model' => $this->model,
                    'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                    'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
                    'functions' => $options['functions'] ?? null
                ]);
            }
            
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate chat responses for conversational AI.
     */
    public function chat(array $conversation, array $options = []): array
    {
        try {
            if($this->stream){
                $response = $this->client->completions()->createStreamed([
                    'model' => $this->model !== config('ai.providers.openai.default_model') ? $this->model : config('ai.providers.openai.chat_model'),
                    'messages' => $conversation,
                    'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
                    'functions' => $options['functions'] ?? null,
                    'stream_options' => [
                        'include_usage' => true,
                    ]
                ]);
            }else{
                $response = $this->client->chat()->create([
                    'model' => $this->model !== config('ai.providers.openai.default_model') ? $this->model : config('ai.providers.openai.chat_model'),
                    'messages' => $conversation,
                    'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
                    'functions' => $options['functions'] ?? null
                ]);
            }
            
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Analyze the sentiment of a given text.
     */
    public function analyzeSentiment(string $text, array $options = []): array
    {
        try {
            $response = $this->client->classifications()->create([
                'model' => $this->model,
                'query' => $text,
                'labels' => $options['labels'] ?? ['Negative, Neutral, Positive'],
                'functions' => $options['functions'] ?? null
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Summarize a given text.
     */
    public function summarizeText(string $text, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $this->model,
                'prompt' => "Summarize the following text:\n\n" . $text,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
                'functions' => $options['functions'] ?? null
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Translate text from one language to another.
     */
    public function translateText(string $text, string $targetLanguage, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $this->model,
                'prompt' => "Translate the following text to {$targetLanguage}:\n\n" . $text,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
                'functions' => $options['functions'] ?? null
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate embeddings for a given text.
     */
    public function generateEmbeddings(string $text, array $options = []): array
    {
        try {
            $response = $this->client->embeddings()->create([
                'model' => $this->model !== config('ai.providers.openai.default_model') ? $this->model : 'text-embedding-ada-002',
                'input' => $text,
                'functions' => $options['functions'] ?? null
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate code or fix code snippets.
     */
    public function generateCode(string $prompt, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $this->model !== config('ai.providers.openai.default_model') ? $this->model : 'code-davinci-002',
                'prompt' => $prompt,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? 0.2,
                'functions' => $options['functions'] ?? null
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
