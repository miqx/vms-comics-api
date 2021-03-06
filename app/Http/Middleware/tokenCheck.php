<?php

namespace App\Http\Middleware;

use Closure;

class tokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->has('api_token') && $request->api_token === 'foo_bar')
        {
            return $next($request);
        }

        return response()->json(['error' => true, 'message' => 'Please provide correct API Token'], 401);
    }
}
