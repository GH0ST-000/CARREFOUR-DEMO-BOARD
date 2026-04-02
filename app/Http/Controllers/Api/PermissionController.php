<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        return PermissionResource::collection($user->getAllPermissions());
    }
}
