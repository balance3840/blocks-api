<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

Trait RoleValidator
{
    public String $roleValidatorMessage = "Name is required";

    public static function validateRole(array $params) {
        $validator = Validator::make($params, [
            'name' => 'required|max:255',
        ]);
        return $validator;
    }
}
