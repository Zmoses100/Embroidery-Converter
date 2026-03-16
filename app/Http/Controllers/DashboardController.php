<?php

namespace App\Http\Controllers;

use App\Models\Conversion;
use App\Models\EmbroideryFile;
use App\Models\Plan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_files'       => $user->embroideryFiles()->count(),
            'total_conversions' => $user->conversions()->count(),
            'storage_used_mb'   => $user->storageUsedMb(),
            'today_conversions' => $user->todayConversionCount(),
            'failed_conversions'=> $user->conversions()->failed()->count(),
        ];

        $recentConversions = $user->conversions()
            ->with(['sourceFile', 'outputFile'])
            ->latest()
            ->limit(5)
            ->get();

        $recentFiles = $user->embroideryFiles()
            ->originals()
            ->latest()
            ->limit(5)
            ->get();

        $plan = $user->activePlan();

        return view('dashboard.index', compact('stats', 'recentConversions', 'recentFiles', 'plan', 'user'));
    }
}
