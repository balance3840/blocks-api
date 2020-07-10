<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait TaskValidator
{
    public static function validateTask(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
            'title' => 'required|max:255',
            'group_id' => 'required|integer',
            'status_id' => 'required|integer',
            'completed_at' => 'date'
        ]);
    }
}
