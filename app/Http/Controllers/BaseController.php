<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function successResponse($data, $message = '', $code = 200) {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function errorResponse($e, $message = '', $code = 500) {
        return response()->json([
            'message' => $e,
            'error' => $message
        ], $code);
    }
}