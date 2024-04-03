<?php

namespace App\Mail;

use App\Game;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GameApprovedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $game;
    public $assignedTime;
    public $receiver;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Game $game, User $receiver)
    {
        $user_timezone = $receiver->timezone;
        $this->game = $game;
        $this->receiver = $receiver;
        $this->assignedTime = (new \Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('NetCon: Partida aprobada')->view('emails.game-approved');
    }
}
