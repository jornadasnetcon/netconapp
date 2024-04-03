<?php
namespace App\Events;

use App\Game;
use Illuminate\Queue\SerializesModels;

class GameApproved
{
    use SerializesModels;

    public $game;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}