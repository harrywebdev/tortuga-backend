<?php

namespace App\Providers;

use App\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        setlocale(LC_MONETARY, 'cs_CZ.UTF-8');

        if (config('tortuga.debug_db')) {
            DB::connection()->enableQueryLog();
            // for printing all queries that ran, use this:
            // dd(\Illuminate\Support\Facades\DB::getQueryLog());
        }
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
            if (Cache::has(AppSettings::CACHE_KEY)) {
                return new AppSettings(Cache::get('settings'));
            }

            // cache settings for a day
            $settings = Cache::remember(AppSettings::CACHE_KEY, 60 * 60 * 24, function () {
                return Settings::all()->pluck('value', 'name')->toArray();
            });

            return new AppSettings($settings);
        });
    }
}
