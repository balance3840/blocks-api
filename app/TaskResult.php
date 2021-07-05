<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskResult extends Model
{

    protected $with = ['status'];

    protected $table = 'tasks_result';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id', 'user_id', 'video', 'code', 'completed_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function status()
    {
        return $this->hasOne('App\Status', 'id', 'status_id');
    }

}
