<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = ''): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $plan = $user->activePlan();

        // If a specific feature is required, check it
        if ($feature && $plan) {
            $allowed = match ($feature) {
                'api'      => $plan->api_access,
                'preview'  => $plan->preview_enabled,
                'batch'    => $plan->max_batch_size > 1,
                default    => true,
            };

            if (!$allowed) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => "Your plan does not include {$feature} access."], 403);
                }

                return redirect()->route('plans.index')
                    ->with('warning', "Your current plan does not include this feature. Please upgrade.");
            }
        }

        return $next($request);
    }
}
