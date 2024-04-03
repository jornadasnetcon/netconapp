<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Talk extends Model
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
        'streamed' => 'boolean',
        'approved' => 'boolean',
    ];

    /**
     * Owner of the game
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }



    public function isOwner(User $user = null)
    {
        if (!$user) {
            return false;
        }

        return $this->owner_id === $user->id;
    }

    public function isApproved()
    {
        return (boolean)$this->approved;
    }

    public function setImageNameAttribute($name) {
        $image_path = explode("/", $name);
        $this->attributes['image_name'] = array_pop($image_path);
    }
}
