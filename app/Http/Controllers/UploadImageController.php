<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class UploadImageController
 *
 * @package App\Http\Controllers
 */
class UploadImageController extends BaseController
{
    /**
     * @var UserService $userService
     */
    protected $userService;

    /**
     * UploadImageController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError(__('auth.invalid_token'));
        }

        $this->validate(
            $request,
            [
                'file'  => 'required|image|mimes:jpg,png|max:2048'
            ]
        );

        return $this->sendResponse(
            $this->userService->uploadUserImage($request->file('file'), $user),
            __('other.image_upload')
        );
    }
}
