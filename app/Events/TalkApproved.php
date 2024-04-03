<?php
namespace App\Events;

use App\Game;
use App\Talk;
use Illuminate\Queue\SerializesModels;

class TalkApproved
{
    use SerializesModels;

    public $game;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Talk $game)
    {
        $this->game = $game;
    }
}