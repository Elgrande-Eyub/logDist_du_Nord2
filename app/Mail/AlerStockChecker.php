<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerStockChecker extends Mailable
{
    use Queueable, SerializesModels;

    public $RaptureDeStock;
    public $AlertDeStock;
    public $NormalDeStock;
    public $ArticleNotInStock;

    public function __construct($RaptureDeStock,$AlertDeStock,$NormalDeStock,$ArticleNotInStock)
    {
        $this->RaptureDeStock = $RaptureDeStock;
        $this->AlertDeStock = $AlertDeStock;
        $this->NormalDeStock = $NormalDeStock;
        $this->ArticleNotInStock = $ArticleNotInStock;
    }


    public function envelope()
    {
        return new Envelope(
            subject: 'Aler Stock Checker',
        );
    }


    public function content()
    {
        return new Content(
            view: 'mail.AlertStockTest',

        );
    }


    public function attachments()
    {
        return [];
    }
}
