<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Kwakuofosuagyeman\AIAssistant\Services\GeminiAIService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;

class GeminiAIServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock configuration
        config([
            'ai.providers.gemini.api_key' => 'test-api-key',
            'ai.providers.gemini.base_url' => 'https://test-gemini-api.com/',
            'ai.providers.gemini.default_model' => 'test-model',
        ]);
    }

    public function testConstructorThrowsExceptionIfConfigIsMissing()
    {
        config([
            'ai.providers.gemini.api_key' => null,
            'ai.providers.gemini.base_url' => null,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        new GeminiAIService();
    }

    public function testGenerateTextReturnsDataOnSuccess()
    {
        $mockClient = Mockery::mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('post')->once()->andReturn(
                new Response(200, [], json_encode(['message' => 'Test response']))
            );
        });

        $service = new GeminiAIService();
        $service->client = $mockClient;

        $response = $service->generateText('Test prompt');

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(['message' => 'Test response'], $response['data']);
    }

    public function testGenerateTextReturnsErrorOnException()
    {
        $mockClient = Mockery::mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('post')->once()->andThrow(new \Exception('Test exception'));
        });

        $service = new GeminiAIService();
        $service->client = $mockClient;

        $response = $service->generateText('Test prompt');

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Test exception', $response['error']);
    }

    public function testTranscribeAudioReturnsDataOnSuccess()
    {
        $mockClient = Mockery::mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('post')
                ->times(3)
                ->andReturn(
                    new Response(200, ['X-Goog-Upload-URL' => ['https://test-upload-url.com']], ''),
                    new Response(200, [], ''),
                    new Response(200, [], json_encode(['message' => 'Transcription response']))
                );
        });

        $service = new GeminiAIService();
        $service->client = $mockClient;

        $audioPath = __DIR__ . '/test-audio.mp3';
        file_put_contents($audioPath, 'fake-audio-content'); // Create a fake audio file for testing

        $response = $service->transcribeAudio($audioPath);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(['message' => 'Transcription response'], $response['data']);

        unlink($audioPath); // Clean up the fake audio file
    }

    public function testTranscribeAudioReturnsErrorOnException()
    {
        $mockClient = Mockery::mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('post')->once()->andThrow(new \Exception('Test exception'));
        });

        $service = new GeminiAIService();
        $service->client = $mockClient;

        $audioPath = __DIR__ . '/test-audio.mp3';
        file_put_contents($audioPath, 'fake-audio-content');

        $response = $service->transcribeAudio($audioPath);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Test exception', $response['error']);

        unlink($audioPath);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
