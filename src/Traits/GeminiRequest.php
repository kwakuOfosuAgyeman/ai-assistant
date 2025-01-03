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
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post', array $headers = [])
    {
        $url = config('ai.providers.gemini.base_url') . $urlSuffix;
    
        try{
            $client = new Client();
            $response = $client->request($method, $url, [
                'headers' => $headers,
                'json' => $data,
            ]);

            return $response;
        }catch(\Exception $e){
            [$e => $e->message()];
        }
        
       
    }

    protected function uploadAudio($file, $filename)
    {
        try{
            // Step 1: Determine MIME Type and File Size
            if (!file_exists($file)) {
                return "Error: File not found at {$file}";
            }

            // Step 2: Start Resumable Upload
            $mimeType = mime_content_type($file);
            $file['mimeType'] = $mimeType;
            $numBytes = filesize($file);
            $displayName = $filename;
            $endpoint = 'upload/v1beta/files?key=' . $this->apiKey;
            $data = [
                'file' => $displayName,
            ];
            $startResponse = $this->sendRequest($endpoint, $data);
            if ($startResponse->getStatusCode() !== 200) {
                throw new \Exception('Error starting upload: ' . $startResponse->getBody()->getContents());
            }
            $uploadUrl = $startResponse->getHeaderLine('X-Goog-Upload-URL');

            if (!$uploadUrl) {
                throw new \Exception('Error: Resumable upload URL not found in response headers.');
            }

            // Step 2: Upload Audio File
            $audioFile = fopen($audioPath, 'r');
            $uploadResponse = $client->post($uploadUrl, [
                'headers' => [
                    'Content-Length' => $numBytes,
                    'X-Goog-Upload-Offset' => 0,
                    'X-Goog-Upload-Command' => 'upload, finalize',
                ],
                'body' => $audioFile,
            ]);
            fclose($audioFile);
            if ($uploadResponse->getStatusCode() !== 200) {
                throw new \Exception('Error uploading audio file: ' . $uploadResponse->getBody()->getContents());
            }

            $file['fileUri'] = $fileInfo['file']['uri'];
            return $this;
        }catch(\Exception $e)
        {
            return ['error' => $e->getMessage()];
        }
    }
}