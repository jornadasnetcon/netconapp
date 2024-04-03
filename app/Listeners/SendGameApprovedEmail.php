<?php

namespace App\Listeners;

use App\Events\GameApproved;
use App\Mail\GameApprovedEmail;
use App\Mail\PlayerRegisteredEmail;
use Illuminate\Support\Facades\Mail;

class SendGameApprovedEmail
{

    public function handle(GameApproved $event)
    {
        $owner = $event->game->owner;
        Mail::to($owner)->send(new GameApprovedEmail($event->game, $owner));
    }
}
