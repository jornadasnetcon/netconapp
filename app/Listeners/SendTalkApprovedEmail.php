<?php

namespace App\Listeners;

use App\Events\GameApproved;
use App\Events\TalkApproved;
use App\Mail\GameApprovedEmail;
use App\Mail\PlayerRegisteredEmail;
use Illuminate\Support\Facades\Mail;

class SendTalkApprovedEmail
{

    public function handle(TalkApproved $event)
    {
        $owner = $event->game->owner;
        Mail::to($owner)->send(new GameApprovedEmail($event->game, $owner));
    }
}
