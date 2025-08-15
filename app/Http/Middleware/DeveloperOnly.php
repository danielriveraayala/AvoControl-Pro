<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeveloperOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'Access denied. Authentication required.');
        }

        $user = auth()->user();
        
        // Simplified check - use email whitelist for developer access
        $developerEmails = [
            'developer@avocontrol.com',
            'admin@avocontrol.com',
            'test.developer@avocontrol.com'
        ];
        
        $hasDeveloperAccess = in_array($user->email, $developerEmails);
        
        // Optional: Try database check as secondary method
        if (!$hasDeveloperAccess) {
            try {
                $hasDeveloperAccess = DB::table('user_role')
                    ->join('roles', 'roles.id', '=', 'user_role.role_id')
                    ->where('user_role.user_id', $user->id)
                    ->where('roles.name', 'super_admin')
                    ->exists();
            } catch (\Exception $e) {
                Log::warning('Database role check failed: ' . $e->getMessage());
                // Continue with whitelist result
            }
        }
        
        if (!$hasDeveloperAccess) {
            Log::warning('Developer access denied for user: ' . $user->email);
            abort(403, 'Access denied. Developer privileges required.');
        }

        Log::info('Developer access granted to: ' . $user->email);

        // Add developer flag to request for use in views
        $request->merge(['is_developer' => true]);

        return $next($request);
    }
}
