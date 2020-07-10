<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait InstituteValidator
{
    public static function validateInstitute(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
            'city' => 'required|max:255',
            'province' => 'required|max:255',
            'address' => 'required|max:255'
        ]);
    }
}
