<?php

namespace App\Providers;

use App\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Tortuga\AppSettings;
use Tortuga\Validation\JsonSchemaValidator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // TODO: locale configurable
        setlocale(LC_MONETARY, 'cs_CZ');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JsonSchemaValidator::class, function ($app) {
            return new JsonSchemaValidator();
        });

        $this->app->singleton(AppSettings::class, function () {
            return new AppSettings(Settings::all()->pluck('value', 'name')->toArray());
        });
    }
}
