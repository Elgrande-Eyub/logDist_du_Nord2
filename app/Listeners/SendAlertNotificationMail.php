<?php

namespace App\Listeners;

use App\Events\AlertStockProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAlertNotificationMail
{
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
     * @param  \App\Events\AlertStockProcessed  $event
     * @return void
     */
    public function handle(AlertStockProcessed $event)
    {
        //
    }
}
