<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', ['plan' => new Plan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request);
        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validate($request);
        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'name'                   => 'required|string|max:100',
            'slug'                   => 'required|string|alpha_dash|max:50',
            'description'            => 'nullable|string|max:500',
            'price_monthly'          => 'required|numeric|min:0',
            'price_yearly'           => 'required|numeric|min:0',
            'stripe_monthly_price_id'=> 'nullable|string|max:100',
            'stripe_yearly_price_id' => 'nullable|string|max:100',
            'conversions_per_day'    => 'required|integer|min:-1',
            'storage_limit_mb'       => 'required|integer|min:1',
            'max_file_size_mb'       => 'required|integer|min:1',
            'max_batch_size'         => 'required|integer|min:1',
            'preview_enabled'        => 'boolean',
            'history_enabled'        => 'boolean',
            'api_access'             => 'boolean',
            'priority_queue'         => 'boolean',
            'is_active'              => 'boolean',
            'is_featured'            => 'boolean',
            'sort_order'             => 'integer|min:0',
        ]);
    }
}
