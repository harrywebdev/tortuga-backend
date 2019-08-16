<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        \App\Events\CustomerRegistered::class => [
            \App\Listeners\LogCustomerRegistered::class,
        ],

        \App\Events\CustomerUpdated::class => [
            \App\Listeners\LogCustomerUpdated::class,
        ],

        \App\Events\OrderReceived::class => [
            \App\Listeners\LogOrderCreated::class,
        ],

        \App\Events\OrderMarkedAsReadyForPickup::class => [
            \App\Listeners\NotifyCustomerAboutOrderReady::class,
        ],

        \App\Events\OrderRejected::class => [
            \App\Listeners\NotifyCustomerAboutOrderRejected::class,
        ],

        \App\Events\OrderDelayed::class => [
            \App\Listeners\NotifyCustomerAboutOrderDelayed::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
