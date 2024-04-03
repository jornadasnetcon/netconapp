<?php

namespace App\Mail;

use App\Game;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriceReceivedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $claim;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $claim = true)
    {
        $this->user = $user;
        $this->claim = $claim;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('NetCon: ¡Te ha tocado un premio!')->view('emails.price-received');
    }
}
