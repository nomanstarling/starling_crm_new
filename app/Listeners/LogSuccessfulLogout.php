<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Traits\LogsActivity;

class LogSuccessfulLogout implements ShouldQueue
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
     * @param  \Logout  $event
     * @return void
     */

    public function handle(Logout $event)
    {
        activity()
            ->causedBy($event->user)
            ->log('User logged out');
    }
}
