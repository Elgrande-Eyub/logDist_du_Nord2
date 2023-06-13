<?php

namespace App\Listeners;

use App\Events\AlertStockProcessed;
use App\Mail\AlerStockChecker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAlertNotificationMail
{

    public function __construct()
    {

    }


    public function handle(AlertStockProcessed $event)
    {
        $inventoryData = $event->Inventory;

        Mail::to('ayoub.baraoui.02@gmail.com')
            ->send(new AlerStockChecker());
    }
}
