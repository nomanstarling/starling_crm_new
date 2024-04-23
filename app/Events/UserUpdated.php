<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // public $user;

    // public function __construct(User $user)
    // {
    //     $this->user = $user;
    // }

    // public function broadcastOn()
    // {
    //     return new Channel('user.'.$this->user->id);
    // }

    // public function broadcastWith()
    // {
    //     return [
    //         'message' => 'Your account has been updated!'
    //     ];
    // }

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return ['my-channel'];
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}
