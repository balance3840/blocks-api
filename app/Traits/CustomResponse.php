<?php


namespace App\Traits;


use Illuminate\Http\Response;

/**
 * Trait CustomResponse
 * @package App\Traits
 */
trait CustomResponse
{

    /**
     * @param string $message
     * @param int $code
     * @return Response
     */
    public static function responseError(string $message, int $code = 400): Response
    {
        return response([
            'message' => $message,
            'status' => $code
        ], $code)
            ->header('Content-Type', 'application/json');
    }

    /**
     * @param $data
     * @param int $code
     * @return Response
     */
    public static function responseSuccess($data, int $code = 200): Response
    {
        return response([
            'data' => $data,
            'status' => $code
        ], $code)
            ->header('Content-Type', 'application/json');
    }

}
