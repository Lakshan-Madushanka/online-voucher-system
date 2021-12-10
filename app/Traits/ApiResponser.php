<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Model;

trait ApiResponser
{
    public function showOne(
        $model,
        int $statusCode = 200,
        string $status = 'ok',
        string $statusMsg = 'Query was successful'
    ) {
        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'data' => $model,
            ],
            $statusCode);
    }

    public function showMany(
        $collection,
        int $statusCode = 200,
        string $status = 'ok',
        string $statusMsg = 'Query was successful'
    ) {
        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'data' => $collection,
            ],
            $statusCode);
    }

    public function showError(
        array $data,
        int $statusCode = 500,
        string $status = 'error',
        string $statusMsg = 'Error occured !'
    ) {
        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'errors' => $data,
            ],
            $statusCode);
    }

    public function showMessage(
        array $data,
        int $statusCode = 200,
        string $status = 'ok',
        string $statusMsg = 'Query was successful !'
    ) {
        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'data' => $data,
            ],
            $statusCode);
    }
}