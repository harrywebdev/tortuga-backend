<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Tortuga\Validation\JsonSchemaValidator;
use Tortuga\Validation\LaravelValidator;
use Tortuga\Validation\Validator;

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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Tortuga\Validation\JsonSchemaValidator', function ($app) {
            return new JsonSchemaValidator();
        });
    }
}
