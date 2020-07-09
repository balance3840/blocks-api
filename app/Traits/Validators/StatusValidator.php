<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait StatusValidator
{
    public static function validateStatus(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
        ]);
    }
}
