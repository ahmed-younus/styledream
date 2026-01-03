<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();

        // Check if admin is active
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Your account has been deactivated.');
        }

        // Check role if specified
        if ($role) {
            $hasPermission = match ($role) {
                'super_admin' => $admin->isSuperAdmin(),
                'admin' => $admin->isAdmin(),
                'moderator' => $admin->canModerateContent(),
                default => false,
            };

            if (!$hasPermission) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized.'], 403);
                }
                abort(403, 'You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }
}
