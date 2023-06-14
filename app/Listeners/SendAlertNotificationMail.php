<?php

namespace App\Listeners;

use App\Events\AlertStockProcessed;
use App\Mail\AlerStockChecker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAlertNotificationMail
{
    // public $articles;
    public function __construct($articles)
    {
        // $this->$articles;
    }


    public function handle()
    {

    }

}
