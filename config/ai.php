<?php


return [
    'default' => 'openai',
    'providers' => [
        'services' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'default_model' => 'text-davinci-003',
                'chat_model' => 'gpt-4',
                'default_max_tokens' => 150,
                'embedding_model' => 'text-embedding-ada-002',
                'default_temperature' => 0.7,
            ],
            'claude' => [
                'api_key' => env('CLAUDE_API_KEY'),
                'base_url' => 'https://api.anthropic.com/v1/messages',
                'default_max_tokens' => 150,
                'model' => 'claude-3-5-sonnet-20241022',
                'version' => 'version',

            ],
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
                'default_model' => 'gemini-1.5-flash:generateContent',
            ]
        ],
    ]
    
];
