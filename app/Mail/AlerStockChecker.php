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


    public function __construct()
    {

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
            view: 'mail.AlertStock',
        );
    }


    public function attachments()
    {
        return [];
    }
}
