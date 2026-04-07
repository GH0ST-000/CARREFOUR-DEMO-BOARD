<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ActionLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(
        private readonly ActionLogService $actionLogService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        if (! $token = Auth::guard('api')->attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();
        $this->actionLogService->logLogin($user->id);

        return $this->respondWithToken($token);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();
        $this->actionLogService->logLogout($user->id);

        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(): UserResource
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        return new UserResource($user);
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
