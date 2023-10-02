<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use Illuminate\Support\ServiceProvider;

class MJMLServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/mjml.php' => config_path('mjml.php'),
        ]);

        $this->app->bind(MJML::class, function () {
            $config = new Config(config('mjml') ?? []);

            return new MJML($config);
        });
    }
}
