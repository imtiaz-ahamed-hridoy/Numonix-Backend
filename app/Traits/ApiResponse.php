<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Provides standardized JSON response methods for API controllers.
 *
 * Includes success, error, and paginated response helpers with
 * consistent status code handling.
 */
Trait ApiResponse
{

    /**
     * Return a success JSON response (alias for success).
     *
     * @param  string  $message  Success message
     * @param  mixed  $data  Response data
     * @param  int  $statusCode  HTTP status code
     * @return JsonResponse
     */
    public function ok(string $message, $data = [], int $statusCode = 200): JsonResponse
    {
        return $this->success($message, $data, $statusCode);
    }

    /**
     * Return a standardized success JSON response.
     *
     * @param  string  $message  Success message
     * @param  mixed  $data  Response data
     * @param  int  $statusCode  HTTP status code
     * @param  array  $extra  Optional extra top-level keys merged into the response
     * @return JsonResponse
     */
    public function success($message, $data = [], $statusCode = 200, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $extra), $statusCode);
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param  string  $message  Error message
     * @param  int  $code  HTTP status code
     * @param  array  $errors  Optional detailed error map
     * @return JsonResponse
     */
    public function error(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors'  => $errors,
            'status' => $code
        ], $code);
    }


    /**
     * Return a JSON response with pagination metadata.
     *
     * @param  string  $message  Response message
     * @param  mixed  $data  Response data items
     * @param  mixed  $paginateData  Paginator instance
     * @param  int  $statusCode  HTTP status code
     * @return JsonResponse
     */
    public function pagination(string $message, $data = null, $paginateData = null, int $statusCode = 200): JsonResponse {
        $response = [
            'status' => $statusCode,
            'message' => $message,
        ];

        $pagination = $paginateData ?? $data;

        if ($pagination instanceof Paginator) {
            $response['data'] = $paginateData ? $data : $pagination->items();
            $response['pagination'] = [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'first_page_url' => $pagination->url(1),
                'last_page_url' => $pagination->url($pagination->lastPage()),
                'next_page_url' => $pagination->nextPageUrl(),
                'prev_page_url' => $pagination->previousPageUrl(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
                'path' => $pagination->path(),
            ];
        } elseif ($data !== null) {
            $response['data'] = $data;
        }
        return response()->json($response, $statusCode);
    }

    /**
     * General json response with pagination
     */
    public function successPagination(bool $status, string $message, int $code, $data = null, bool $paginate = false, $paginateData = null): JsonResponse
    {
        $response = [
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
            ];

        // Determine which pagination object to use
        $pagination = $paginateData ?? $data;

        if ($paginate && $pagination instanceof Paginator) {
            $response['data'] = $paginateData ? $data : $pagination->items();
            $response['pagination'] = [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'first_page_url' => $pagination->url(1),
                'last_page_url' => $pagination->url($pagination->lastPage()),
                'next_page_url' => $pagination->nextPageUrl(),
                'prev_page_url' => $pagination->previousPageUrl(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
                'path' => $pagination->path(),
            ];
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

}
