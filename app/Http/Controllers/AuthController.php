<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.verify')->except(['login', 'register']);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'image_name' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Wrong data provided', $validator->messages(), 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);
        $user->role()->associate(Role::findOrFail($input['role_id']));

        $data = [
            'token' => auth()->login($user),
            'user' => $user,
        ];

        return $this->sendResponse($data, 'User registered successfully');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
//        JWTAuth::factory()->setTTL(1);
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:4|max:30'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Wrong data provided', $validator->messages(), 422);
        }

        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError('Wrong credentials', [], 401);
        }

        return $this->sendResponse(['token' => $token], 'Log in successfully');
    }

    /**
     * @return JsonResponse
     */
    public function refreshToken(): JsonResponse
    {
        return $this->sendResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendResponse([
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role->name
            ]
        ]);
    }

    /**
     * Log out function
     */
    public function logout()
    {
        auth()->logout();
    }
}
