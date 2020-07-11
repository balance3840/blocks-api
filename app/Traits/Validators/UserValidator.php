<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait UserValidator
{
    public static function validateUser(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'email|unique:users|max:255',
            'role_id' => 'required|integer',
            'institute_id' => 'required|integer'
        ]);
    }

    public static function validateUserCreate(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|max:255',
            'role_id' => 'required|integer',
            'institute_id' => 'required|integer'
        ]);
    }
}
