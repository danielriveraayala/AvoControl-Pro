<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|array  $permissions  Required permissions (can be single permission or array)
     * @param  string  $logic  'AND' or 'OR' logic for multiple permissions (default: 'AND')
     */
    public function handle(Request $request, Closure $next, ...$args): Response
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
        
        // If no permissions specified, just check if authenticated
        if (empty($args)) {
            return $next($request);
        }

        // Parse arguments: permissions and logic
        $permissions = [];
        $logic = 'AND'; // default logic
        
        foreach ($args as $arg) {
            if (in_array(strtoupper($arg), ['AND', 'OR'])) {
                $logic = strtoupper($arg);
            } else {
                $permissions[] = $arg;
            }
        }

        if (empty($permissions)) {
            return $next($request);
        }

        // Check permissions based on logic
        $hasPermission = false;
        
        if ($logic === 'OR') {
            // User needs ANY of the specified permissions
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $hasPermission = true;
                    break;
                }
            }
        } else {
            // User needs ALL of the specified permissions (AND logic)
            $hasPermission = true;
            foreach ($permissions as $permission) {
                if (!$user->hasPermission($permission)) {
                    $hasPermission = false;
                    break;
                }
            }
        }

        if (!$hasPermission) {
            // Log unauthorized access attempt
            \Log::warning('Unauthorized permission access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_permissions' => $permissions,
                'logic' => $logic,
                'user_permissions' => $user->getAllPermissions(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient privileges. Required permissions ({$logic}): " . implode(', ', $permissions)
                ], 403);
            }

            abort(403, 'No tienes los permisos necesarios para realizar esta acción.');
        }

        return $next($request);
    }
}
