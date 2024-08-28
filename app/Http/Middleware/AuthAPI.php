<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization');
        $accessToken = ltrim($authorization,"Bearer ");
        $accessTokenData = Cache::get("api_token_".$accessToken);
        if(!$accessTokenData) {
            response()->json([
                'api_status' => 0,
                'api_message' => 'Forbidden Access!',
                'http_status' => 403
            ], 403)->send();
            exit;
        }
        return $next($request);
    }
}
