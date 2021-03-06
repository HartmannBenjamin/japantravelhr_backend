<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Exception;
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
     * Here's the user service
     *
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
     * Upload new image for user
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $this->validate(
                $request,
                [
                    'file'  => 'required|image|mimes:jpg,png|max:2048'
                ]
            );

            $user = $this->userService->uploadUserImage($request->file('file'), $user);
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $this->sendResponse($user, __('other.image_upload'));
    }
}
