<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Service\RolePolicyService;
class moderatorAccsess
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
        if (auth('api')->user() == null) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $roles = RolePolicyService::getUsreRoles(auth('api')->user()->id);
        if(!in_array('moderator',$roles))
        {
            return response()->json([
                'message' => 'You are not moderator'
            ], 401);
        }
        return $next($request);
        
    }
}
