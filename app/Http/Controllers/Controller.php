<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param int    $status
     * @param string $title
     * @param string $detail
     * @param string $pointer
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _returnError($status = 404, $title = 'Not Found', $detail = 'Not Found', $pointer = '/')
    {
        $errorData = [
            'status' => $status,
            'source' => (object)['pointer' => $pointer],
            'title'  => $title,
            'detail' => $detail,
        ];

        Log::channel('sentry')->error("$status: $title", $errorData);

        return response()->json((object)['errors' => [(object)$errorData]], $status);
    }
}
