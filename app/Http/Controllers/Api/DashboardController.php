<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LoginLog;
use App\Models\Product;
use App\Models\Service;
use App\Models\VisitorLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * This endpoint does not require any permissions, only authentication
     */
    public function index(Request $request)
    {
        $timeRange = $request->get('time_range', '7d');
        $startDate = $this->getStartDateForRange($timeRange);

        // Basic stats
        $servicesCount = Service::count();
        $productsCount = Product::count();
        $newLeadsCount = Lead::where('status', 'new')->count();

        // Recent leads (last 10)
        $recentLeads = Lead::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'type' => $lead->type,
                    'status' => $lead->status,
                    'is_read' => $lead->is_read,
                    'created_at' => $lead->created_at->toISOString(),
                ];
            });

        // Visitor statistics
        $visitorStats = $this->getVisitorStatistics($startDate);

        // Login statistics
        $loginStats = $this->getLoginStatistics($startDate);

        return response()->json([
            'stats' => [
                'services' => $servicesCount,
                'products' => $productsCount,
                'leads' => $newLeadsCount,
            ],
            'recent_leads' => $recentLeads,
            'visitor_stats' => $visitorStats,
            'login_stats' => $loginStats,
        ]);
    }

    /**
     * Get visitor statistics
     */
    private function getVisitorStatistics($startDate)
    {
        $totalVisits = VisitorLog::count();
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

        // Time-series trends data
        $trendsData = $this->getVisitorTrendsData($startDate);

        return [
            'total' => $totalVisits,
            'bot_visits' => $botVisits,
            'human_visits' => $humanVisits,
            'device_stats' => $deviceStats,
            'browser_stats' => $browserStats,
            'top_pages' => $topPages,
            'trends' => $trendsData,
        ];
    }

    /**
     * Get login statistics
     */
    private function getLoginStatistics($startDate)
    {
        $totalLogs = LoginLog::count();
        $successfulLogins = LoginLog::where('status', 'success')->count();
        $failedLogins = LoginLog::where('status', 'failed')->count();

        return [
            'total' => $totalLogs,
            'successful' => $successfulLogins,
            'failed' => $failedLogins,
        ];
    }

    /**
     * Get visitor trends data (time-series)
     */
    private function getVisitorTrendsData($startDate)
    {
        $days = [];
        $totalVisits = [];
        $humanVisits = [];

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

            $totalVisits[] = $total;
            $humanVisits[] = $human;

            $currentDate->addDay();
        }

        return [
            'labels' => $days,
            'total' => $totalVisits,
            'human' => $humanVisits,
        ];
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
}

