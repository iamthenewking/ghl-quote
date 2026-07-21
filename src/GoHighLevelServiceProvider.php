<?php

declare(strict_types=1);

namespace Iamthenewking\GhlQuote;

use Illuminate\Support\ServiceProvider;

class GoHighLevelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gohighlevel.php', 'gohighlevel');

        $this->app->singleton(GoHighLevelClient::class, function ($app) {
            $config = $app['config']['gohighlevel'];

            return new GoHighLevelClient(
                (string) ($config['token'] ?? ''),
                (string) ($config['location_id'] ?? ''),
                (string) ($config['api_base'] ?? 'https://services.leadconnectorhq.com'),
            );
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'gohighlevel');

        if (config('gohighlevel.register_routes', true)) {
            $this->app['router']
                ->middleware(config('gohighlevel.route_middleware', ['web']))
                ->prefix(config('gohighlevel.route_prefix', 'ghl'))
                ->group(__DIR__ . '/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/gohighlevel.php' => config_path('gohighlevel.php'),
            ], 'gohighlevel-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/gohighlevel'),
            ], 'gohighlevel-views');
        }
    }
}
