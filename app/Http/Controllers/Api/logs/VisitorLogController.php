<?php

namespace App\Http\Controllers\Api\logs;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;

class VisitorLogController extends Controller
{
    public function index(Request $request)
    {
        $query = VisitorLog::query();

        // Filter by device type
        if ($request->has('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Filter by browser
        if ($request->has('browser')) {
            $query->where('browser', $request->browser);
        }

        // Filter by OS
        if ($request->has('os')) {
            $query->where('os', $request->os);
        }

        // Filter bots
        if ($request->has('is_bot')) {
            $query->where('is_bot', $request->is_bot === 'true' || $request->is_bot === '1');
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('referer', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'ip_address', 'url', 'device_type', 'browser', 'os', 'created_at'];
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

    public function show(VisitorLog $visitorLog)
    {
        return response()->json($visitorLog);
    }

    public function destroy(VisitorLog $visitorLog)
    {
        $visitorLog->delete();
        return response()->json(['message' => 'Visitor log deleted successfully']);
    }

    /**
     * Delete multiple visitor logs
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:visitor_logs,id',
        ]);

        VisitorLog::whereIn('id', $request->ids)->delete();
        
        return response()->json(['message' => 'Visitor logs deleted successfully']);
    }

    /**
     * Get statistics about visitor logs
     */
    public function statistics(Request $request)
    {
        $timeRange = $request->get('time_range', '7d'); // 7d, 30d, 90d, 1y
        $startDate = $this->getStartDateForRange($timeRange);
        
        $totalVisits = VisitorLog::count();
        $uniqueIPs = VisitorLog::whereNotNull('ip_address')->distinct('ip_address')->count('ip_address');
        $botVisits = VisitorLog::where('is_bot', true)->count();
        $humanVisits = VisitorLog::where('is_bot', false)->count();
        
        // Device type statistics
        $deviceStats = VisitorLog::selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();
        
        // Browser statistics
        $browserStats = VisitorLog::selectRaw('browser, COUNT(*) as count')
            ->where('is_bot', false)
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'browser')
            ->toArray();
        
        // OS statistics
        $osStats = VisitorLog::selectRaw('os, COUNT(*) as count')
            ->where('is_bot', false)
            ->groupBy('os')
            ->orderByDesc('count')
            ->pluck('count', 'os')
            ->toArray();
        
        // Recent activity (last 24 hours)
        $recentVisits = VisitorLog::where('created_at', '>=', now()->subDay())->count();
        $recentHumanVisits = VisitorLog::where('is_bot', false)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $recentBotVisits = VisitorLog::where('is_bot', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Most visited pages
        $topPages = VisitorLog::selectRaw('url, COUNT(*) as visits')
            ->where('is_bot', false)
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'url' => $item->url,
                    'visits' => $item->visits,
                ];
            });

        // Time-series data for trends
        $trendsData = $this->getTrendsData($startDate);

        return response()->json([
            'total' => $totalVisits,
            'unique_ips' => $uniqueIPs,
            'bot_visits' => $botVisits,
            'human_visits' => $humanVisits,
            'device_stats' => $deviceStats,
            'browser_stats' => $browserStats,
            'os_stats' => $osStats,
            'recent' => [
                'total' => $recentVisits,
                'human' => $recentHumanVisits,
                'bot' => $recentBotVisits,
            ],
            'top_pages' => $topPages,
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
        $totalVisits = [];
        $humanVisits = [];
        $botVisits = [];
        
        $currentDate = \Carbon\Carbon::parse($startDate);
        $endDate = now();
        
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $days[] = $dateStr;
            
            // Get visits for this day
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            $total = VisitorLog::whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $human = VisitorLog::where('is_bot', false)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            $bot = VisitorLog::where('is_bot', true)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            
            $totalVisits[] = $total;
            $humanVisits[] = $human;
            $botVisits[] = $bot;
            
            $currentDate->addDay();
        }
        
        return [
            'labels' => $days,
            'total' => $totalVisits,
            'human' => $humanVisits,
            'bot' => $botVisits,
        ];
    }
}
