<?php

namespace  App\Traits;

trait Responses
{
    public function success(int $status = 200, string $message, array|object|null $data = [], array $extra = [])
    {
        $response = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($extra)) {
            $response['extra'] = $extra;
        }

        return response()->json($response, $status);
    }

    public function successPaginated(int $status = 200, string $message = 'Success', array|object $data, array $extra = [])
    {
        $currentPage = $data->currentPage();
        $perPage = $data->perPage();
        $total = $data->total();
        $lastPage = $data->lastPage();

        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'next_page_url' => $data->nextPageUrl(),
            'prev_page_url' => $data->previousPageUrl(),
            'current_page_url' => $data->url($currentPage),
        ];

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => ['items' => $data->items(), 'extra' => $extra],
            'pagination' => $pagination,
        ]);
    }


    public function error(int $status = 500, string $message, $code = "", array $errors = [], array|object $data = [])
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'code' => $code,
        ];

        // Add errors if present (for validation errors)
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        // Add data if present (for contextual/debug information)
        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }
}
