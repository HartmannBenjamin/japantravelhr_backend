<?php

namespace App\Services;

use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Class UserService
 *
 * @package App\Services
 */
class UserService
{

    /**
     * @param $file
     * @param $user
     *
     * @return UserResource
     */
    public function uploadUserImage(UploadedFile $file, User $user): UserResource
    {
        $imageName = time() . '_' . $file->getClientOriginalName();
        $imageResize = Image::make($file->getRealPath());
        $imageResize->resize(300, 300);

        $imageResize->save(public_path('images') . '/' . $imageName);

        if ($user->image_name && $user->image_name != 'test.png') {
            File::delete('images/' . $user->image_name);
        }

        $user->image_name = $imageName;
        $user->save();

        return new UserResource($user);
    }
}
