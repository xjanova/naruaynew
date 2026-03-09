<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveMemberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->active_status === 'blocked') {
            abort(403, 'Your account has been blocked. Contact support.');
        }

        return $next($request);
    }
}
