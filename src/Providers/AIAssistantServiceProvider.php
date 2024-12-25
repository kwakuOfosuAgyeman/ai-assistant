<?php

namespace Kwakuofosuagyeman\AIAssistant\Providers;

use Illuminate\Support\ServiceProvider;
use Kwakuofosuagyeman\AIAssistant\AIManager;

class AIAssistantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ai.php', 'ai');

        $this->app->singleton('ai', function () {
            return new AIManager(config('ai'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/ai.php' => config_path('ai.php'),
        ], 'config');
    }
}
