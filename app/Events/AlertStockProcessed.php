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
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    // public $articles;

    public function __construct()
    {
        $this->handle();
    }


    public function handle()
    {


        // $this->articles = Article::get();


        $Articles = Article::get();

        $RaptureDeStock = [];
        $AlertDeStock = [];
        $NormalDeStock = [];
        $ArticleNotInStock = [];

        foreach($Articles as $Article) {

            $inventory = Inventory::leftjoin('articles', 'inventories.article_id', '=', 'articles.id')->where('article_id', $Article->id)
            ->select('inventories.*', 'articles.*')->first();

            if ($inventory == null) {
                array_push($ArticleNotInStock, $Article);
            } elseif ($Article->alert_stock >= $inventory->actual_stock && $inventory->actual_stock != 0) {
                array_push($AlertDeStock, $inventory);
            } elseif ($inventory->actual_stock == 0) {
                array_push($RaptureDeStock, $inventory);
            } else {
                array_push($NormalDeStock, $inventory);
            }
        }


        Mail::to('ayoub.baraoui.02@gmail.com')
        ->send(new AlerStockChecker($RaptureDeStock, $AlertDeStock, $NormalDeStock, $ArticleNotInStock));



    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

}
