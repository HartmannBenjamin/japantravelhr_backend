<?php

namespace App\Services;

use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
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

    /**
     * @param array $input
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateRegisterData(array $input)
    {
        return Validator::make(
            $input,
            [
                'name' => 'required|min:4|max:50',
                'email' => 'required|email|min:4|max:100',
                'role_id' => 'required|integer|min:1|max:5',
                'password' => 'required|min:4|max:50',
                'c_password' => 'required|same:password|min:4|max:50',
            ]
        );
    }

    /**
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateLoginData(array $credentials)
    {
        return Validator::make(
            $credentials,
            [
                'email' => 'required|email|min:4|max:100',
                'password' => 'required|string|min:4|max:50'
            ]
        );
    }

    /**
     * @param array $input
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateChangePasswordData(array $input)
    {
        return Validator::make(
            $input,
            [
                'password' => 'required|min:4|max:50',
                'c_password' => 'required|same:password|min:4|max:50',
            ]
        );
    }
}
