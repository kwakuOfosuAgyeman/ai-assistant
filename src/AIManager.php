<?php

namespace Kwakuofosuagyeman\AIAssistant;

use Kwakuofosuagyeman\AIAssistant\Services\OpenAI;
use Kwakuofosuagyeman\AIAssistant\Services\HuggingFace;
use Kwakuofosuagyeman\AIAssistant\Services\Claude;
use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;

class AIManager
{
    protected array $services = [];

    /**
     * Resolve an AI service dynamically based on the provider.
     *
     * @param string|null $provider
     * @return AIService
     * @throws \InvalidArgumentException
     */
    public function resolveService(?string $provider = null): AIService
    {
        $provider = $provider ?? config('ai.default');
        $config = config("ai.providers.$provider");

        if (!$config || empty($config['api_key'])) {
            throw new \InvalidArgumentException("Invalid provider or missing API key: $provider");
        }

        // Check if service is already instantiated (singleton behavior).
        if (isset($this->services[$provider])) {
            return $this->services[$provider];
        }

        // Create the service instance.
        $service = match ($provider) {
            'openai' => new OpenAIService(['api_key' => $config['api_key']]),
            'huggingface' => new HuggingFaceService($config['api_key']),
            'claude' => new ClaudeService($config['api_key']),
            default => throw new \InvalidArgumentException("Unsupported AI provider: $provider"),
        };

        // Cache and return the instance.
        return $this->services[$provider] = $service;
    }

    /**
     * Shortcut method for a specific service method (e.g., text generation).
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters = [])
    {
        $provider = $parameters['provider'] ?? null;
        unset($parameters['provider']);

        $service = $this->resolveService($provider);

        if (!method_exists($service, $method)) {
            throw new \BadMethodCallException("Method $method does not exist in the resolved AI service.");
        }

        return $service->$method(...$parameters);
    }
}
