<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemIssued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;

    public function __construct($location)
    {
        $this->location = $location;
    }


    public function broadcastOn()
    {
        // Channel = user doesn't need to authenticated or authorize
        return new Channel('issued');
    }

    public function broadcastWith()
    {
        return [
            'location' => $this->location
        ];
    }
}
