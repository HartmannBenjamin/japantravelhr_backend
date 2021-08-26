<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UploadImageController extends BaseController
{
    protected $userService;

    /**
     * Instantiate a new controller instance.
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
     *
     * @throws ValidationException
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $this->validate($request, [
            'file'  => 'required|image|mimes:jpg,png|max:2048'
        ]);

        return $this->sendResponse(
            ['user' => $this->userService->uploadUserImage($request->file('file'), $request->user())],
            __('other.image_upload')
        );
    }
}
