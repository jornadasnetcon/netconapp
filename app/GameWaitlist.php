<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameWaitlist extends Model
{
  public function __construct(array $attributes = []) 
  {
    $this->table = "game_waitlist";
  }
}
