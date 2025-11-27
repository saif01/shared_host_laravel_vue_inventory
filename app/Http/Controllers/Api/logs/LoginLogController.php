<?php

namespace App\Http\Controllers\Api\logs;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginLog::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('failure_reason', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'email', 'ip_address', 'status', 'created_at', 'logged_in_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = $request->get('per_page', 10);
        $logs = $query->paginate($perPage);
        
        return response()->json($logs);
    }

    public function show(LoginLog $loginLog)
    {
        $loginLog->load('user');
        return response()->json($loginLog);
    }

    public function destroy(LoginLog $loginLog)
    {
        $loginLog->delete();
        return response()->json(['message' => 'Login log deleted successfully']);
    }

    /**
     * Get statistics about login logs
     */
    public function statistics(Request $request)
    {
        $timeRange = $request->get('time_range', '7d'); // 7d, 30d, 90d, 1y
        $startDate = $this->getStartDateForRange($timeRange);
        
        $totalLogs = LoginLog::count();
        $successfulLogins = LoginLog::where('status', 'success')->count();
        $failedLogins = LoginLog::where('status', 'failed')->count();
        $uniqueUsers = LoginLog::whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $uniqueIPs = LoginLog::whereNotNull('ip_address')->distinct('ip_address')->count('ip_address');
        
        // Recent activity (last 24 hours)
        $recentLogs = LoginLog::where('created_at', '>=', now()->subDay())->count();
        $recentSuccessful = LoginLog::where('status', 'success')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $recentFailed = LoginLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Time-series data for trends
        $trendsData = $this->getTrendsData($startDate);

        return response()->json([
            'total' => $totalLogs,
            'successful' => $successfulLogins,
            'failed' => $failedLogins,
            'unique_users' => $uniqueUsers,
            'unique_ips' => $uniqueIPs,
            'recent' => [
                'total' => $recentLogs,
                'successful' => $recentSuccessful,
                'failed' => $recentFailed,
            ],
            'trends' => $trendsData,
        ]);
    }

    /**
     * Get start date based on time range
     */
    private function getStartDateForRange($timeRange)
    {
        switch ($timeRange) {
            case '30d':
                return now()->subDays(30);
            case '90d':
                return now()->subDays(90);
            case '1y':
                return now()->subYear();
            case '7d':
            default:
                return now()->subDays(7);
        }
    }

    /**
     * Get time-series trends data
     */
    private function getTrendsData($startDate)
    {
        $days = [];
        $successful = [];
        $failed = [];
        
        $currentDate = \Carbon\Carbon::parse($startDate);
        $endDate = now();
        
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $days[] = $dateStr;
            
            // Get logins for this day
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            $successfulCount = LoginLog::where('status', 'success')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            $failedCount = LoginLog::where('status', 'failed')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            
            $successful[] = $successfulCount;
            $failed[] = $failedCount;
            
            $currentDate->addDay();
        }
        
        return [
            'labels' => $days,
            'successful' => $successful,
            'failed' => $failed,
        ];
    }
}
