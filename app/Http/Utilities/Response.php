<?php

namespace App\Http\Utilities;

trait Response
{
    /**
     * @param $data
     * @param $message
     * @param $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendJsonResponse($data, $message, $statusCode)
    {
        return response()->json(['data' => $data, 'message' => $message], $statusCode, ['content-type: application/json']);
    }

    /**
     * @param $callback
     * @param $data
     * @param $message
     * @param $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendJsonpResponse($callback, $data, $message, $statusCode)
    {
        return response()->jsonp($callback, ['data' => $data, 'message' => $message], $statusCode, ['content-type: application/json']);
    }
}
