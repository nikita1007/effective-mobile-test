<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\JwtTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    use JwtTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем jwt-токен
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Проверяем валидность токена и пользователя
        $user_id = $this->jwt_decode($token)['user_id'];

        if (User::find($user_id)->doesntExist()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->add(['user_id' => $user_id]);

        return $next($request);
    }
}
