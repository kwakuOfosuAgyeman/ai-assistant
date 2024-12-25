<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use OpenAI\Client;
use Psr\Log\LoggerInterface;
use Exception;

class OpenAIService implements AIService
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Generate text based on a given prompt.
     */
    public function generateText(string $prompt, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $options['model'] ?? config('ai.providers.openai.default_model'),
                'prompt' => $prompt,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error generating text', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Generate chat responses for conversational AI.
     */
    public function chat(string $conversation, array $options = []): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $options['model'] ?? config('ai.providers.openai.chat_model'),
                'messages' => $options['messages'],
                'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error generating chat', [
                'error' => $e->getMessage(),
                'conversation' => $conversation,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Analyze the sentiment of a given text.
     */
    public function analyzeSentiment(string $text, array $options = []): array
    {
        try {
            $response = $this->client->classifications()->create([
                'model' => $options['model'] ?? config('ai.providers.openai.default_model'),
                'query' => $text,
                'labels' => ['Positive', 'Neutral', 'Negative'],
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error analyzing sentiment', [
                'error' => $e->getMessage(),
                'text' => $text,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Summarize a given text.
     */
    public function summarizeText(string $text, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $options['model'] ?? config('ai.providers.openai.default_model'),
                'prompt' => "Summarize the following text:\n\n" . $text,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error summarizing text', [
                'error' => $e->getMessage(),
                'prompt' => "Summarize the following text:\n\n" . $text,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Translate text from one language to another.
     */
    public function translateText(string $text, string $targetLanguage, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $options['model'] ?? config('ai.providers.openai.default_model'),
                'prompt' => "Translate the following text to {$targetLanguage}:\n\n" . $text,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.providers.openai.default_temperature'),
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error translating text', [
                'error' => $e->getMessage(),
                'prompt' => "Translate the following text to {$targetLanguage}:\n\n" . $text,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Generate embeddings for a given text.
     */
    public function generateEmbeddings(string $text, array $options = []): array
    {
        try {
            $response = $this->client->embeddings()->create([
                'model' => $options['model'] ?? 'text-embedding-ada-002',
                'input' => $text,
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error generating embeddings', [
                'error' => $e->getMessage(),
                'text' => $text,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }

    /**
     * Generate code or fix code snippets.
     */
    public function generateCode(string $prompt, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $options['model'] ?? 'code-davinci-002',
                'prompt' => $prompt,
                'max_tokens' => $options['max_tokens'] ?? config('ai.providers.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? 0.2,
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('OpenAIService: Error generating code', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'options' => $options,
            ]);
            return ['error' => 'An error occurred while processing the request.'];
        }
    }
}
