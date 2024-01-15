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

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }


    public function broadcastOn()
    {
        // Channel = user doesn't need to authenticated or authorize
        return new Channel('issued');
    }

    // this function allows us to share data in the frontend
    // public function broadcastWith()
    // {
    //     $latestIssuedRequest = RequestStocks::orderBy('id', 'DESC')->first();

    //     return ['latestIssuedRequest' => $latestIssuedRequest];
    // }
}
