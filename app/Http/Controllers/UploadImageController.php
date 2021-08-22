<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadImageController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $file = $request->file;

        if ($file) {
            $imageName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $imageName);

            $user = User::where('email', $request->get('userEmail'))->first();
            $user->image_name = $imageName;
            $user->save();

            return $this->sendResponse($user, 'Image uploaded.');
        }

        return $this->sendError('No image provided.');
    }
}
