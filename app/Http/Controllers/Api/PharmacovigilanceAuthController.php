<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmacovigilanceLoginRequest;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PharmacovigilanceAuthController extends Controller
{
    use ResponseTrait;

    public function login(PharmacovigilanceLoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->responseError(null, 'Invalid credentials.', Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::guard('api')->user();

        return $this->responseSuccess([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
            ],
        ], 'Login successful.');
    }
}
