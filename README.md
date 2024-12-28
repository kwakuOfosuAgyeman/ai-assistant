# AI Service Package

This package provides a unified interface to interact with various AI services, including OpenAI, Gemini and Claude. It is designed for Laravel applications and offers an extensible and configurable solution for AI-powered functionality such as text generation, embeddings, and more.

---

## Features

- **Multi-Provider Support**: Interact with OpenAI, Gemini, and Claude.
- **Centralized Configuration**: Manage settings from a single `config/ai.php` file.
- **Extensible Interface**: Add support for new AI providers with ease.
- **Error Handling**: Includes `try...catch` blocks to handle API errors gracefully.
- **Laravel Compatible**: Designed to work seamlessly with Laravel’s dependency injection and service container.

---

## Installation

### Step 1: Require the Package
```bash
composer require kwakuofosuagyeman/ai-assistant
```

### Step 2: Publish Configuration File
Publish the configuration file to customize settings:
```bash
php artisan vendor:publish --tag=ai-config
```

This will create a `config/ai.php` file in your Laravel application.

### Step 3: Set Environment Variables
Add your API keys and configuration to the `.env` file:

```env
YOUR_PROVIDER_API_KEY=your-provider-api-key
```

---

## Configuration

The `config/ai.php` file contains all the settings for the supported AI providers. Example:

```php
return [
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'default_model' => 'text-davinci-003',
            'chat_model' => 'gpt-4',
            'embedding_model' => 'text-embedding-ada-002',
            'default_temperature' => 0.7,
        ],
        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'base_url' => 'https://api.anthropic.com/v1/',
            'default_max_tokens' => 150,
            'model' => 'claude-3-5-sonnet-20241022',
            'version' => 'version',
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
            'default_model' => 'gemini-1.5-flash:generateContent',
        ]
    ]
    
];
```

---

## Usage

### Method 1: Use the Service in Your Application
Use the required service where needed, such as in controllers or other services:

```php
use Kwakuofosuagyeman\AIAssistant\Services\OpenAIService;

class ExampleController extends Controller
{
    protected $aiManager;


    public function generateText(Request $request)
    {
        $prompt = $request->input('prompt');

        try {
            $openAIService = new OpenAIService();
            $response = $openAIService->generateText("Write a poem about the sea.");

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

```

### Method 2: Use the Service in Your Application (If you wish to use multiple AI providers)
Inject the `AIService` interface where needed, such as in controllers or other services:

```php
use Kwakuofosuagyeman\AIAssistant\AIManager;

class ExampleController extends Controller
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    public function generateText(Request $request)
    {
        $provider = $request->input('provider'); // Optional, defaults to config('ai.default')
        $prompt = $request->input('prompt');

        try {
            $response = $this->aiManager->resolveService($provider)->generateText($prompt);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

```



---

## Supported Methods

### OpenAI

### This system uses `openai-php/client` to aid in making requests to openai

### `generateText(string $prompt, array $options = []): array`
Generates text based on the given prompt.

```php
$response = $aiService->resolveService('openai')->generateText('Tell me a story about space exploration.', [
    'temperature' => 0.8,
    'max_tokens' => 200,
]);

echo $response['data'];
```

### `generateEmbeddings(string $text): array`
Generates embeddings for the given text. This uses the `text-embedding-ada-002` by default.

```php
$response = $aiService->resolveService('openai')->generateEmbeddings('Artificial Intelligence');

print_r($response['data']);
```

### `chat(array $messages, array $options = []): array`
Facilitates a conversation-like interaction with the AI model.

```php
$messages = [
    ['role' => 'user', 'content' => 'What is the capital of France?'],
    ['role' => 'assistant', 'content' => 'The capital of France is Paris.'],
];

$response = $aiService->resolveService('openai')->chat($messages);

echo $response['data']['content'];
```

### `analyzeSentiment(string $text, array $options = []): array`
Analyze the sentiment of a text. The labels given to the ai are ['negative', 'neutral', 'positive']

```php
$text = 'Text to analyze';

$response = $aiService->resolveService('openai')->analyzeSentiment($text);
```

### `summarizeText(string $text, array $options = []): array`
Transcribes audio files.

```php
$text = 'Text to summarize';

$response = $aiService->resolveService('openai')->summarizeText($text);
```

### `translateText(string $text, string $targetLanguage, array $options = []): array`
Translates text from its current language to the targetLanguage.

```php
$text = 'Text to translate';
$targetLangugae = 'French';

$response = $aiService->resolveService('openai')->translateText($text, $targetLanguage);
```

### `generateCode(string $prompt, array $options = []): array`
Generates code. This has a default temperature of 0.2

```php
$text = 'Generate python code to reverse a string';


$response = $aiService->resolveService('openai')->generateCode($prompt);
```

### Gemini

### `generateText(string $prompt, array $options = []): array`
Generates text based on the given prompt.

```php
$response = $aiService->resolveService('gemini')->generateText('Tell me a story about space exploration.');

echo $response['data'];
```

### `transcribeAudio(string $audioPath, array $options = []): array`
Transcribes the given audio into text. 

```php
$audio = "path/to/audio";


$response = $aiService->resolveService('gemini')->transcribeAudio($audio);

```

### Claude



---

## Adding a New AI Provider

To add support for a new AI provider:

1. **Create a New Service**:
   Implement the `AIService` interface:

   ```php
   namespace Kwakuofosuagyeman\AIAssistant\Services;

   use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;

   class NewAIProviderService implements AIService
   {
       public function generateText(string $prompt, array $options = []): array
       {
           // Implement text generation
       }

       // Implement other methods as needed
   }
   ```

2. **Register the New Service**:
   Bind the new service in your service provider.

---

## Testing

You can test the service using PHPUnit. Example test for the OpenAI integration:

```php
use Tests\TestCase;
use Kwakuofosuagyeman\AIAssistant\Contracts\AIService;

class OpenAIServiceTest extends TestCase
{
    public function testGenerateText()
    {
        $aiService = $this->app->make(AIService::class);
        $response = $aiService->resolveService('openai')->generateText('Test prompt');

        $this->assertArrayHasKey('data', $response);
    }
}
```

---

## Contributing

Contributions are welcome! Please follow the standard Laravel and PHP development guidelines.

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Submit a pull request with a clear description of your changes.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Support

For issues, please open an issue on the GitHub repository or contact the maintainers.

