<?php

namespace App\Providers;

use App\Services\Ai\Contracts\ChatResponder;
use App\Services\Ai\RuleBasedResponder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Swap this binding for an LLM-backed implementation later without
        // touching controllers, routes, or the frontend.
        $this->app->bind(ChatResponder::class, RuleBasedResponder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('ai-chat', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: 'guest:'.$request->ip());
        });
    }
}
