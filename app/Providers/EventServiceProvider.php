<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\MessageSent' => [
            'App\Listeners\EmailMessageToGameParticipants',
        ],
        'App\Events\PlayerRegistered' => [
            'App\Listeners\SendPlayerRegisteredEmail',
        ],
        'App\Events\PlayerUnregistered' => [
            'App\Listeners\SendPlayerUnregisteredEmail',
        ],
        'App\Events\WaitlistPlayerRegistered' => [
            'App\Listeners\SendEmailWaitlistPlayerRegistered',
        ],
        'App\Events\GameApproved' => [
            'App\Listeners\SendGameApprovedEmail',
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
