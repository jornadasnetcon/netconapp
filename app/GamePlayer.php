<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
  public function __construct(array $attributes = []) 
  {
    $this->table = "game_player";
  }
}
