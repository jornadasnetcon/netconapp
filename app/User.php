<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use Notifiable;

    private $cache;

    protected $table = 'users';

    protected $admins = [1,2,3,4,5];

    //21 perez.hermida@gmail.com 
    //76 80 102 103
    protected $bans = [
                        '21' => ['76', '80', '102', '103'],
                      ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'about', 'provider', 'provider_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getNameAttribute($value) {
        if (!$value) {
            return 'Netconner' . $this->id;
        }

        return $value;
    }

    public function games() {
        return $this->hasMany(Game::class, 'owner_id');
    }

    public function talks() {
        return $this->hasMany(Talk::class, 'owner_id');
    }

    public function prices() {
        return $this->hasMany(Price::class, 'user_id');
    }

    public function signupGames() {
        return $this->belongsToMany(Game::class, 'game_player', 'player_id', 'game_id');
    }

    public function waitlistGames() {
        return $this->belongsToMany(Game::class, 'game_waitlist', 'waitlist_id', 'game_id');
    }

    public function favGames() {
        return $this->belongsToMany(Game::class, 'game_player_fav', 'player_id', 'game_id')->withPivot('priority');
    }

    public function isAdmin() {
    	return in_array($this->id, $this->admins);
    }

    public function isTester() {
        $testers = explode(',', getenv('AUTHORIZED_ORGANIZATION_EMAILS'));
        return in_array($this->email, $testers);
    }

    public function isBusy($game) {//return true;
        $start = $game->starting_time;
        $end = date('Y-m-d H:i:s', strtotime($game->starting_time."+".$game->duration_hours."hours"));
        //$signup
        foreach ($this->signupGames()->where('game_id', '!=', $game->id)->get() as $registered) {
            $regStart = $registered->starting_time;
            $regEnd = date('Y-m-d H:i:s', strtotime($registered->starting_time."+".$registered->duration_hours."hours"));
            if ($start >= $regStart && $start <= $regEnd) {
                return $registered;
            } elseif ($end >= $regStart && $end <= $regEnd) {
                return $registered;
            } if ($regStart >= $start && $regStart <= $end) {
                return $registered;
            } elseif ($regEnd >= $start && $regEnd <= $end) {
                return $registered;
            }
        }
        return false;
    }

    public function isBanned ($game) {
        if (isset($this->bans[$this->id]) && in_array($game->id, $this->bans[$this->id])) {
            return true;
        }
        return false;
    }
}
