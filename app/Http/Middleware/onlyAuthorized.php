<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class onlyAuthorized
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
    
        //if (auth('api')->user() == null)
        if(auth('api')->user() == null)
		{
            return response()->json([
                        'message' => 'Unauthorized'
                    ], 401);
		}
        else
        {
        return $next($request);
        }
   }
}
