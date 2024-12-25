<?php

namespace Kwakuofosuagyeman\AIAssistant\Services;

use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;
use OpenAI\Client;
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
                'model' => $options['model'] ?? config('ai.openai.default_model'),
                'prompt' => $prompt,
                'max_tokens' => $options['max_tokens'] ?? config('ai.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.openai.default_temperature'),
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate chat responses for conversational AI.
     */
    public function chat(string $conversation, array $options = []): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $options['model'] ?? 'gpt-4',
                'messages' => $options['messages'],
                'temperature' => $options['temperature'] ?? config('ai.openai.default_temperature'),
            ]);
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
                'model' => $options['model'] ?? 'text-davinci-003',
                'query' => $text,
                'labels' => ['Positive', 'Neutral', 'Negative'],
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
                'model' => $options['model'] ?? 'text-davinci-003',
                'prompt' => "Summarize the following text:\n\n" . $text,
                'max_tokens' => $options['max_tokens'] ?? config('ai.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.openai.default_temperature'),
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
                'model' => $options['model'] ?? 'text-davinci-003',
                'prompt' => "Translate the following text to {$targetLanguage}:\n\n" . $text,
                'max_tokens' => $options['max_tokens'] ?? config('ai.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.openai.default_temperature'),
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
                'model' => $options['model'] ?? 'text-embedding-ada-002',
                'input' => $text,
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Perform question answering on a given context and question.
     */
    public function answerQuestion(string $context, string $question, array $options = []): array
    {
        try {
            $response = $this->client->completions()->create([
                'model' => $options['model'] ?? 'text-davinci-003',
                'prompt' => "Answer the question based on the context below:\n\nContext: {$context}\n\nQuestion: {$question}",
                'max_tokens' => $options['max_tokens'] ?? config('ai.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? config('ai.openai.default_temperature'),
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
                'model' => $options['model'] ?? 'code-davinci-002',
                'prompt' => $prompt,
                'max_tokens' => $options['max_tokens'] ?? config('ai.openai.default_max_tokens'),
                'temperature' => $options['temperature'] ?? 0.2,
            ]);
            return $response->toArray();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
