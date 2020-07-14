<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'grade', 'level', 'stage_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the stage record associated with the group.
     */
    public function stage()
    {
        return $this->hasOne('App\Stage', 'id', 'stage_id');
    }

    public function members() {
        return $this->belongsToMany('App\User', 'user_groups', 'group_id', 'user_id');
    }

    public function tasks() {
        return $this->hasMany('App\Task', 'group_id', 'id');
    }

}
