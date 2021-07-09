<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{

    protected $with = ['user'];

    protected $table = 'task_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'task_id', 'comment'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    
    /**
     * Get the author of the comment
     */
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

}
