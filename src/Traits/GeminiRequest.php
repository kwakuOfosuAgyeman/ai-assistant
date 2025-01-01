<?php

namespace KwakuofosuAgyeman\AIAssistant\Traits;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

trait GeminiRequest
{
    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return array
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post')
    {
        $url = config('') . $urlSuffix;

        if (!empty($data['stream']) && $data['stream'] === true) {
            $client = new Client();
            $response = $client->request($method, $url, [
                'json' => $data,
                'stream' => true,
            ]);

            return $response;
        } else {
            $response = Http::timeout(config('ollama-laravel.connection.timeout'))->$method($url, $data);
            return $response->json();
        }
    }
}