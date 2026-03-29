<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        try {
            AuditLog::log('auth.registered', $user->id, User::class, $user->id, [], [
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write audit log for registration', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }

        // Optional welcome email
        if (Setting::get('send_welcome_email', true)) {
            try {
                $user->notify(new WelcomeEmail());
            } catch (\Throwable $e) {
                Log::warning('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return redirect(route('dashboard'));
    }
}
