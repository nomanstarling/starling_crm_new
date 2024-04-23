<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Traits\LogsActivity;

class LogSuccessfulLogin implements ShouldQueue
{
    use InteractsWithQueue, LogsActivity;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Login  $event
     * @return void
     */
    // public function handle(IlluminateAuthEventsLogin $event)
    // {
    //     //
    // }
    public function handle(Login $event)
    {
        activity()
            ->causedBy($event->user)
            ->log('User logged in');
    }
}
