<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'starting_time'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'duration_hours' => 'integer',
        'sessions_number' => 'integer',
        'maximum_players_number' => 'integer',
        'signedup_players_number' => 'integer',
        'streamed' => 'boolean',
        'approved' => 'boolean',
        'open_signups' => 'boolean',
        'children_created' => 'boolean',
        'safety_tools' => 'string',
        'session_no' => 'integer',
    ];

    function getTitleAttribute($value) {
        $session_number = $this->session_no;
        return !$this->isMultipleSessions() ? $value : $value . " (Sesión $session_number)";
    }
    /**
     * Owner of the game
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }


    /**
     * Parent game
     */
    public function parent() {
        return $this->belongsTo(Game::class, 'parent_id');
    }

    /**
     * Players registered to play in the game
     */
    public function players()
    {
        return $this->belongsToMany(User::class, 'game_player', 'game_id', 'player_id');
    }

    /**
     * Players registered in the waitlist of the game
     */
    public function waitlist()
    {
        return $this->belongsToMany(User::class, 'game_waitlist', 'game_id', 'waitlist_id')->orderBy('game_waitlist.id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'game_id');
    }

    public function isOwner(User $user = null)
    {
        if (!$user) {
            return false;
        }

        return $this->owner_id === $user->id;
    }

    public function isFull()
    {
        return $this->maximum_players_number <= $this->signedup_players_number;
    }

    public function isRegistered(User $user = null)
    {
        if (!$user) {
            return false;
        }

        return $this->players()->where('users.id', $user->id)->first() ? true : false;
    }

    public function isWaitlisted(User $user = null)
    {
        if (!$user) {
            return false;
        }

        return $this->waitlist()->where('users.id', $user->id)->first() ? true : false;
    }

    public function isApproved()
    {
        return (boolean)$this->approved;
    }

    public function isPartial() {
        return $this->session_no > 1;
    }

    public function canRegister(User $user = null)
    {
        if (!$user) {
            return false;
        }

        if (!$this->isApproved()) {
            return false;
        }

        if ($this->isFull()) {
            return false;
        }

        if ($this->isRegistered($user)) {
            return false;
        }

        if ($this->isOwner($user)) {
            return false;
        }

        if ($this->isPartial()) {
            return false;
        }

        if ($user->isBusy($this)) {
            return false;
        }

        if ($user->isBanned($this)) {
            return false;
        }

        return true;
    }

    public function canWaitlist(User $user = null)
    {
        if (!$user) {
            return false;
        }

        if (!$this->isApproved()) {
            return false;
        }

        if ($this->isOwner($user)) {
            return false;
        }

        if ($this->isPartial()) {
            return false;
        }

        if ($this->isWaitlisted($user)) {
            return false;
        }

        if ($this->isRegistered($user)) {
            return false;
        }

        if ($this->isFull()) {
            return true;
        }

        if ($user->isBusy($this)) {
            return false;
        }

        if ($user->isBanned($this)) {
            return false;
        }

        return false;
    }

    public function canRegisterToWaitlist(User $user = null)
    {
        if (!$user) {
            return false;
        }

        if (!$this->isApproved()) {
            return false;
        }

        if ($this->isRegistered($user)) {
            return false;
        }

        if ($this->isOwner($user)) {
            return false;
        }

        if (!$this->isFull()) {
            return false;
        }

        if ($user->isBusy($this)) {
            return false;
        }

        if ($user->isBanned($this)) {
            return false;
        }

        return true;
    }

    public function canReadMessages(User $user = null)
    {
        if (!$user) {
            return false;
        }

        if ($this->isRegistered($user)) {
            return true;
        }

        if ($this->isOwner($user)) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function canCreateMessage(User $user = null)
    {
        if (!$user) {
            return false;
        }

        if ($this->isRegistered($user)) {
            return true;
        }

        if ($this->isOwner($user)) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function getStatusAttribute()
    {
        return $this->maximum_players_number <= $this->signedup_players_number ? 'Lleno' : 'Disponible';
    }

    public function setImageNameAttribute($name) {
        $image_path = explode("/", $name);
        $this->attributes['image_name'] = array_pop($image_path);
    }

    public function isMultipleSessions() {
        return $this->sessions_number > 1;
    }
}
