<?php

namespace Kwakuofosuagyeman\AIAssistant;

use Kwakuofosuagyeman\AIAssistant\Services\OpenAI;
use Kwakuofosuagyeman\AIAssistant\Services\HuggingFace;
use Kwakuofosuagyeman\AIAssistant\Services\Claude;

class AIManager
{
    protected $services = [];

    public function __construct(array $config)
    {
        $this->services['openai'] = new OpenAI($config['services']['openai']);
        $this->services['huggingface'] = new HuggingFace($config['services']['huggingface']);
        $this->services['claude'] = new Claude($config['services']['claude']);
    }

    public function service(string $name = null)
    {
        $name = $name ?? config('ai.default');
        return $this->services[$name] ?? null;
    }
}
