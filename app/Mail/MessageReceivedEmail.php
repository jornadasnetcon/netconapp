<?php

namespace App\Mail;

use App\Game;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MessageReceivedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $game;

    public $sender;

    public $receiver;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Game $game, User $sender, User $receiver)
    {
        $this->game = $game;
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(env('BRAND_NAME','NetCon').': Mensaje Recibido')->view('emails.message-received');
    }
}
