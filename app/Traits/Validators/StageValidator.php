<?php


namespace App\Traits\Validators;


use Illuminate\Support\Facades\Validator;

trait StageValidator
{
    public static function validateStage(array $params)
    {
        return Validator::make($params, [
            'name' => 'required|max:255',
        ]);
    }
}
