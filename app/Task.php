<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'title', 'description', 'group_id', 'status_id', 'completed_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the group record associated with the task.
     */
    public function group()
    {
        return $this->hasOne('App\Group', 'id');
    }


    /**
     * Get the status record associated with the task.
     */
    public function status()
    {
        return $this->hasOne('App\Status', 'id');
    }

}
