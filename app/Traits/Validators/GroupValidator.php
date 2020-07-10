<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait GroupValidator
{
    public static function validateGroup(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
            'grade' => 'required|integer',
            'level' => 'required|integer',
            'stage_id' => 'required|integer'
        ]);
    }
}
