<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Usage: ->middleware('role:admin') or ->middleware('permission:users.create')
     * Usage: ->middleware('role:admin,manager') or ->middleware('permission:users.create,users.edit')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type, string $permissions): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a esta página.');
        }

        $user = Auth::user();
        $permissionsArray = explode(',', $permissions);

        // Check based on type (role or permission)
        if ($type === 'role') {
            if (!$user->hasAnyRole($permissionsArray)) {
                return $this->unauthorized($request);
            }
        } elseif ($type === 'permission') {
            if (!$user->hasAnyPermission($permissionsArray)) {
                return $this->unauthorized($request);
            }
        } else {
            // Invalid middleware usage
            abort(500, 'Invalid middleware usage. Use "role" or "permission".');
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access.
     */
    private function unauthorized(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        return redirect()->back()
            ->with('error', 'No tienes permisos para acceder a esta página.')
            ->withInput();
    }
}
