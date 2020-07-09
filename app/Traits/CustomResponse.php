<?php


namespace App\Traits;


use Illuminate\Http\Response;

trait CustomResponse
{

    public static function responseError(string $message, int $code = 400): Response
    {
        return response([
            'message' => $message,
            'status' => $code
        ], $code)
            ->header('Content-Type', 'application/json');
    }

    public static function responseSuccess(object $data, int $code = 200): Response
    {
        return response([
            'data' => $data,
            'status' => $code
        ], $code)
            ->header('Content-Type', 'application/json');
    }

}
