<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public const PER_PAGE = 20;

    /**
     * Success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($message, $result = null, $opt = [])
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result,
        ];

        $response += $opt;

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error = '', $code = 500, $opt = [])
    {
        $error = $error == '' ? 'Unexpected error occurred. Please contact administrator.' : $error;

        $response = [
            'status' => false,
            'message' => $error,
        ];

        $response += $opt;

        return response()->json($response, $code);
    }
}
