<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait RoleValidator
{
    public static function validateRole(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
        ]);
    }
}
