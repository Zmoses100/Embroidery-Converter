<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use App\Models\EmbroideryFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $stats = [
            'total_users'        => User::count(),
            'new_users_month'    => User::whereMonth('created_at', now()->month)->count(),
            'total_conversions'  => Conversion::count(),
            'conversions_today'  => Conversion::whereDate('created_at', today())->count(),
            'failed_conversions' => Conversion::failed()->count(),
            'total_files'        => EmbroideryFile::count(),
            'storage_used_gb'    => round(EmbroideryFile::sum('size_bytes') / 1073741824, 2),
            'active_subscribers' => $this->getActiveSubscriberCount(),
        ];

        $recentUsers = User::latest()->limit(10)->get();

        $recentConversions = Conversion::with(['user', 'sourceFile'])
            ->latest()
            ->limit(10)
            ->get();

        $conversionsByDay = Conversion::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $topFormats = Conversion::selectRaw('target_format, count(*) as count')
            ->groupBy('target_format')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentUsers', 'recentConversions', 'conversionsByDay', 'topFormats'
        ));
    }

    private function getActiveSubscriberCount(): int
    {
        try {
            return DB::table('subscriptions')
                ->where('stripe_status', 'active')
                ->distinct('user_id')
                ->count('user_id');
        } catch (\Exception $e) {
            return 0;
        }
    }
}
