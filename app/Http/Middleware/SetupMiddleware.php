<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // If no admin user exists and not already on setup page, redirect to setup
        if (!$this->isSetupRoute($request) && !$this->adminExists()) {
            return redirect('/setup');
        }

        // If admin exists and trying to access setup, redirect away
        if ($this->isSetupRoute($request) && $this->adminExists()) {
            return redirect('/');
        }

        return $next($request);
    }

    private function adminExists(): bool
    {
        try {
            return User::where('user_level', 0)->exists();
        } catch (\Exception $e) {
            // Table might not exist yet
            return false;
        }
    }

    private function isSetupRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'setup');
    }
}
