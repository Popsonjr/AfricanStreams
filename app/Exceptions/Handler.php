<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler {
    protected $dontReport = [];

    public function render($request, Throwable $exception) {
        if($request->is('api/*')) {
            if($exception instanceof ValidationException) {
                return new JsonResponse([
                    'status_code' => 422,
                    'status_message' => 'Validation failed',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof HttpException) {
                return new JsonResponse([
                    'status_code' => $exception->getStatusCode(),
                    'status_message' => $exception->getMessage() ?: 'Error',
                ], $exception->getStatusCode());
            }

            return new JsonResponse([
                'status_code' => 500,
                'status_message' => 'Server Error',
            ], 500);
        }

        return parent::render($request, $exception);
    }
}