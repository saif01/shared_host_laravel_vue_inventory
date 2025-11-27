<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // If no permissions specified, deny access
        if (empty($permissions)) {
            return response()->json(['message' => 'Permission required'], 403);
        }

        // Check if user has any of the required permissions
        // Also check if user has administrator role (has all permissions)
        $hasPermission = false;
        
        // Check if user has administrator role (full access)
        if ($user->hasRole('administrator')) {
            $hasPermission = true;
        } else {
            // Check if user has any of the required permissions through their roles
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $hasPermission = true;
                    break;
                }
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to perform this action.',
                'required_permissions' => $permissions
            ], 403);
        }

        return $next($request);
    }
}
