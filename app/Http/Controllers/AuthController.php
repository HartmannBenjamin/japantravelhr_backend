<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Role as RoleResource;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers
 */
class AuthController extends BaseController
{
    /**
     * @var UserService $userService
     */
    protected $userService;

    /**
     * AuthController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->middleware('jwt.verify')->except(['login', 'register', 'emailAvailable', 'getRoles']);
        $this->userService = $userService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = $this->userService->validateRegisterData($input);

        if ($validator->fails()) {
            return $this->sendError(__('auth.wrong_data'), $validator->errors(), 422);
        }

        if (User::where('email', '=', $input['email'])->exists()) {
            return $this->sendError(__('auth.email_exists'));
        }

        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::create($input);
            $user->role_id = $input['role_id'];
            $user->save();
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $this->sendResponse(
            ['token' => auth()->login($user), 'user' => new UserResource($user)],
            __('auth.user_created'),
            201
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $validator = $this->userService->validateLoginData($credentials);

        if ($validator->fails()) {
            return $this->sendError(__('auth.wrong_data'), $validator->errors(), 422);
        }

        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError(__('auth.wrong_credentials'), [], 401);
        }

        return $this->sendTokenResponse($token);
    }

    /**
     * @return JsonResponse
     *
     * @throws JWTException
     */
    public function refreshToken(): JsonResponse
    {
        if ($token = JWTAuth::parseToken()->refresh()) {
            return $this->sendTokenResponse($token);
        }

        return $this->sendError(__('auth.refresh_token_error'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return $this->sendResponse(new UserResource($request->user()), __('auth.user_info'));
    }

    /**
     * Log out function
     */
    public function logout()
    {
        auth()->logout();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function emailAvailable(Request $request): JsonResponse
    {
        $isAvailable = true;
        $email = $request->get('email');

        if (User::where('email', '=', $email)->exists()) {
            $isAvailable = false;
        }

        return $this->sendResponse(
            $isAvailable,
            $isAvailable ? __('auth.email_available') : __('auth.email_not_available')
        );
    }

    /**
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse
    {
        return $this->sendResponse(RoleResource::collection(Role::all()), __('auth.roles'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();
        $input = $request->all();

        if ($input['name'] == null || $input['name'] == '') {
            return $this->sendError(__('auth.no_name_provided'));
        }

        if (isset($input['password'])) {
            $validator = $this->userService->validateChangePasswordData($input);

            if ($validator->fails()) {
                return $this->sendError(__('auth.wrong_data'), $validator->errors(), 422);
            }

            $user->password = bcrypt($input['password']);
        }

        try {
            $user->name = $input['name'];
            $user->save();
        } catch (Exception $e) {
            return $this->sendError(__('other.error'), [$e->getMessage()]);
        }

        return $this->sendResponse(
            new UserResource($user),
            __('auth.user_updated')
        );
    }
}
