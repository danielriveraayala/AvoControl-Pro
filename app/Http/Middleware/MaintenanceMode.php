<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Si es el panel de desarrollador, nunca aplicar mantenimiento
        if ($request->is('developer*') || $request->is('api/developer*')) {
            return $next($request);
        }

        // Verificar si el mantenimiento está activo
        $maintenanceActive = Cache::get('frontend_maintenance', false);
        
        if ($maintenanceActive) {
            // Si tienen la clave secreta en la sesión, permitir acceso
            if (session('maintenance_bypass') === true) {
                return $next($request);
            }

            // Mostrar página de mantenimiento
            return response()->view('maintenance', [
                'retry_after' => Cache::get('maintenance_retry_after', 3600),
                'message' => Cache::get('maintenance_message', 'Sistema en mantenimiento temporal')
            ], 503);
        }

        return $next($request);
    }
}