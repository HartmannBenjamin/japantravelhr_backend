<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller as Controller;

/**
 * Class BaseController
 *
 * @package App\Http\Controllers
 */
class BaseController extends Controller
{
    /**
     * Success response method
     *
     * @param array  $result
     * @param string $message
     * @param int    $code
     *
     * @return JsonResponse
     */
    public function sendResponse($result = [], $message = '', $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Success token response method
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    public function sendTokenResponse(string $token): JsonResponse
    {
        return response()->json(['success' => 'true', 'token' => $token])->header('Authorization', $token);
    }

    /**
     * Return error response
     *
     * @param $error
     * @param array $errorMessages
     * @param int   $code
     *
     * @return JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
