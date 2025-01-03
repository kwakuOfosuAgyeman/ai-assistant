<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Kwakuofosuagyeman\AIAssistant\Services\ClaudeAIService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class ClaudeAIServiceTest extends TestCase
{
    protected $mockClient;
    protected $claudeService;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->claudeService = $this->getMockBuilder(ClaudeAIService::class)
            ->onlyMethods(['createHttpClient'])
            ->getMock();

        $this->claudeService->method('createHttpClient')->willReturn($this->mockClient);

        config([
            'ai.providers.claude.api_key' => env('CLAUDE_API_KEY'),
            'ai.providers.claude.base_url' => 'https://api.anthropic.com/v1/',
            'ai.providers.claude.model' => 'claude-3-5-sonnet-20241022',
            'ai.providers.claude.version' => '2023-06-01',
        ]);
    }

    public function testConstructorThrowsExceptionForMissingConfig()
    {
        config(['ai.providers.claude.api_key' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API key, Version and base URL are required in configuration.');

        new ClaudeAIService();
    }

    public function testGenerateTextReturnsResponse()
    {
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $this->mockClient
            ->method('post')
            ->willReturn($mockResponse);

        $messages = [['role' => 'user', 'content' => 'Hello, Claude!']];
        $result = $this->claudeService->generateText($messages);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testGenerateTextHandlesApiError()
    {
        $this->mockClient
            ->method('post')
            ->willThrowException(new RequestException('Error!', new \GuzzleHttp\Psr7\Request('POST', '/')));

        $messages = [['role' => 'user', 'content' => 'Hello, Claude!']];
        $result = $this->claudeService->generateText($messages);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Error!', $result['error']);
    }

    public function testAnalyzeDocumentWithQueryHandlesMissingFile()
    {
        $fileUrl = 'nonexistent.pdf';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unable to fetch the file from {$fileUrl}");

        $this->claudeService->analyzeDocumentWithQuery($fileUrl, []);
    }

    public function testAnalyzeDocumentWithQueryReturnsResponse()
    {
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $this->mockClient
            ->method('post')
            ->willReturn($mockResponse);

        $fileUrl = __DIR__ . '/test.pdf';
        file_put_contents($fileUrl, 'dummy content');

        $result = $this->claudeService->analyzeDocumentWithQuery($fileUrl, ['query' => 'Analyze this document']);
        unlink($fileUrl);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testUseToolHandlesInvalidTool()
    {
        $messages = [['role' => 'user', 'content' => 'Use this tool']];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tool is a required option in configuration.');

        $this->claudeService->useTool($messages, []);
    }

    public function testUseToolReturnsResponse()
    {
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $this->mockClient
            ->method('post')
            ->willReturn($mockResponse);

        $messages = [['role' => 'user', 'content' => 'Use this tool']];
        $result = $this->claudeService->useTool($messages, ['tool' => 'test_tool']);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testBatchMessageMethodsReturnResponse()
    {
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $this->mockClient
            ->method('get')
            ->willReturn($mockResponse);

        $token = 'batch_token';
        $result = $this->claudeService->getBatchMessages($token);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testListBatchMessagesReturnsResponse()
    {
        $mockResponse = new Response(200, [], json_encode(['batches' => []]));
        $this->mockClient
            ->method('get')
            ->willReturn($mockResponse);

        $result = $this->claudeService->listBatchMessages();

        $this->assertArrayHasKey('batches', $result);
        $this->assertIsArray($result['batches']);
    }

    public function testCancelMessageBatchReturnsResponse()
    {
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $this->mockClient
            ->method('get')
            ->willReturn($mockResponse);

        $token = 'batch_token';
        $result = $this->claudeService->cancelMessageBatch($token);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
}
