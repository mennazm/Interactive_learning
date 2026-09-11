<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Contracts\ASRServiceInterface::class,
            \App\Services\BrowserASRService::class
        );
        $this->app->bind(
            \App\Services\Contracts\LLMServiceInterface::class,
            \App\Services\OpenAILLMService::class
        );
        $this->app->bind(
            \App\Services\Contracts\TTSServiceInterface::class,
            \App\Services\ElevenLabsTTSService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
