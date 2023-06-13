<?php

namespace App\Events;

use App\Models\Inventory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertStockProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $Inventory;

    public function __construct()
    {

    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

}
