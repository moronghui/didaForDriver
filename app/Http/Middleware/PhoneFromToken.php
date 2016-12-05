<?php
namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class PhoneFromToken
{
  /**
   *@author Arius
   *@function tel validate
   *
   *
   */
    public function handle($request, Closure $next)
    {
        $user=JWTAuth::toUser();
        if ($user['tel']) {
          return $next($request);
        }
        return response()->json([
            'code' => '204',
            'message' => 'tel_not_set',
            'data' => '',
        ]);
    }
}
