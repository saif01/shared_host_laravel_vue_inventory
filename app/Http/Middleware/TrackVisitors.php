<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VisitorLog;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip tracking for admin routes and API routes
        if ($request->is('admin/*') || $request->is('api/*')) {
            return $next($request);
        }

        // Skip tracking for static assets
        if ($request->is('*.css') || $request->is('*.js') || $request->is('*.jpg') || 
            $request->is('*.png') || $request->is('*.gif') || $request->is('*.svg') ||
            $request->is('*.ico') || $request->is('*.woff') || $request->is('*.woff2')) {
            return $next($request);
        }

        $response = $next($request);

        // Only track successful GET requests
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            try {
                $userAgent = $request->userAgent();
                $isBot = VisitorLog::isBot($userAgent);

                // Optionally skip bots (uncomment if you don't want to track bots)
                // if ($isBot) {
                //     return $response;
                // }

                VisitorLog::create([
                    'ip_address' => $request->ip(),
                    'user_agent' => $userAgent,
                    'url' => $request->fullUrl(),
                    'referer' => $request->header('referer'),
                    'method' => $request->method(),
                    'device_type' => VisitorLog::detectDeviceType($userAgent),
                    'browser' => VisitorLog::detectBrowser($userAgent),
                    'os' => VisitorLog::detectOS($userAgent),
                    'is_bot' => $isBot,
                    'page_views' => 1,
                ]);
            } catch (\Exception $e) {
                // Silently fail to not interrupt the request
                // Log error if needed: \Log::error('Visitor tracking failed: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
