<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|array  $roles  Required roles (can be single role or array of roles)
     * @param  string  $guard  Guard to check (optional)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a esta página.');
        }

        $user = auth()->user();
        
        // If no roles specified, just check if authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Convert single role to array for uniform processing
        $requiredRoles = is_array($roles[0]) ? $roles[0] : $roles;
        
        // Check if user has any of the required roles
        $hasRole = false;
        foreach ($requiredRoles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            // Log unauthorized access attempt
            \Log::warning('Unauthorized role access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_roles' => $requiredRoles,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient privileges. Required roles: ' . implode(', ', $requiredRoles)
                ], 403);
            }

            abort(403, 'No tienes los permisos necesarios para acceder a esta página.');
        }

        return $next($request);
    }
}
