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
            'huggingface' => [
                'api_key' => env('HUGGINGFACE_API_KEY'),
                'base_url' => 'https://api-inference.huggingface.co/models/',
            ],
            'claude' => [
                'api_key' => env('CLAUDE_API_KEY'),
                'base_url' => 'https://api.anthropic.com/v1/'
            ]
        ],
    ]
    
];
