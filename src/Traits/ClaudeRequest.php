<?php

namespace KwakuofosuAgyeman\AIAssistant\Traits;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

trait ClaudeRequest
{
    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return array
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post', array $headers = []): array
    {
        $url = config('ai.providers.claude.base_url') . $urlSuffix;
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'x-api-key' => config('ai.providers.claude.api_key'),
            'anthropic-version' => config('ai.providers.claude.version'),
        ];

        $client = new Client();
        try {
            $response = $client->request($method, $url, [
                'headers' => array_merge($defaultHeaders, $headers),
                'json' => $data,
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