<?php

namespace App\Events;

use App\Listeners\SendAlertNotificationMail;
use App\Mail\AlerStockChecker;
use App\Models\Article;
use App\Models\Inventory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class AlertStockProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {

        Mail::to('ayoub.baraoui.02@gmail.com')
        ->send(new AlerStockChecker());
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

}
