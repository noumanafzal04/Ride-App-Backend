<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $e);
        }        

        // Validation errors
        if ($e instanceof ValidationException) {
            return ApiResponse::error(
                'Validation failed',
                422,
                $e->errors(),
                $e,
            );
        }

        // Authentication
        if ($e instanceof AuthenticationException) {
            return ApiResponse::error(
                'Unauthenticated',
                401,
                null,       
                $e,         
            );
        }

        // Authorization
        if ($e instanceof AuthorizationException) {
            return ApiResponse::error(
                'You are not authorized to perform this action',
                403,
                null,      
                $e,          
            );
        }

        // Not Found
        if (
            $e instanceof ModelNotFoundException ||
            $e instanceof NotFoundHttpException
        ) {
            return ApiResponse::error(
                'Resource not found',
                404,
                null,      
                $e,          
            );
        }

        // Database errors
        if ($e instanceof QueryException) {
            report($e);

            return ApiResponse::error(
                'Database operation failed',
                500,
                null,         
                $e,       
            );
        }

        // HTTP exceptions (429, 503, etc.)
        if ($e instanceof HttpExceptionInterface) {
            return ApiResponse::error(
                $e->getMessage() ?: 'HTTP error',
                $e->getStatusCode(),
                null,        
                $e,        
            );
        }

        if ($e instanceof ApiException) {
            return ApiResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 500,
                $e->payload ?: null,
                $e
            );
        }

        // Unknown errors
        report($e);

        return ApiResponse::error(
            'Internal server error',
            500,
            null,      
            $e,      
        );
    }        
}