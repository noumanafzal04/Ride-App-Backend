<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        if ($data instanceof JsonResource) {
            return $data
                ->additional([
                    'success' => true,
                    'message' => $message,
                    'meta' => self::meta(),
                ])
                ->response()
                ->setStatusCode($status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::meta(),
        ], $status);
    }

    public static function error(
        string $message,        
        int $status = 400,
        mixed $errors = null,
        ?Throwable $exception = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => self::meta(),
        ];
        if (config('app.debug') && $exception) {
            $response['debug'] = [
                'exception' => class_basename($exception),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                'trace'     => collect($exception->getTrace())->take(5),
            ];
        }

        return response()->json($response, $status);        
    }

    public static function noContent(string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'meta' => self::meta(),
        ], 200);
    }

    protected static function meta(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'version'   => request()->attributes->get('version'),
            'trace_id'  => request()->attributes->get('trace_id'),
        ];
    }
}
