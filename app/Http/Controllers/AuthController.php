<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify')->except(['login', 'register']);
    }

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
            return response()->json(['error' => $validator->messages()], 200);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $user->role()->associate(Role::findOrFail($input['role_id']));

        return response()->json([
            'token' => auth()->login($user),
            'user' => $user,
            'message' => 'User registered successfully'
        ], 200);
    }

    public function login(Request $request): JsonResponse
    {
        JWTAuth::factory()->setTTL(1);
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:4|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(null, 401);
        }

        return response()->json(['token' => $token], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role->name
            ]
        ], 200);
    }

    public function logout()
    {
        auth()->logout();
    }
}
