<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withTrashed()
            ->when($request->search, fn($q, $s) => $q->where(function($qq) use ($s) {
                $qq->where('name', 'ilike', "%{$s}%")->orWhere('email', 'ilike', "%{$s}%");
            }))
            ->when($request->filter === 'admin', fn($q) => $q->where('is_admin', true))
            ->when($request->filter === 'deleted', fn($q) => $q->onlyTrashed())
            ->withCount(['conversions', 'embroideryFiles'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->loadCount(['conversions', 'embroideryFiles']);
        $user->load(['conversions' => fn($q) => $q->latest()->limit(10)]);

        return view('admin.users.show', compact('user'));
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['admin' => 'You cannot modify your own admin status.']);
        }

        $user->update(['is_admin' => !$user->is_admin]);

        return back()->with('success', $user->is_admin ? 'User promoted to admin.' : 'Admin role removed from user.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'You cannot delete your own account from here.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function restore(int $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();

        return back()->with('success', 'User restored.');
    }
}
