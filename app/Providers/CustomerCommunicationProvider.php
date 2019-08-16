<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tortuga\SMS\Messenger;
use Tortuga\TextMessaging\GoSMSMessenger;

class CustomerCommunicationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Messenger::class, GoSMSMessenger::class);

        $this->app->singleton(GoSMSMessenger::class, function ($app) {
            return new GoSMSMessenger(
                config('services.go_sms.client_id'),
                config('services.go_sms.client_secret'),
                ['info' => config('services.go_sms.channel_info'), 'reply' => config('services.go_sms.channel_reply')]
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
