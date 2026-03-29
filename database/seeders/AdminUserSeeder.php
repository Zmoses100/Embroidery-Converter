<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        $admin = User::withTrashed()->firstOrNew(['email' => $adminEmail]);

        if ($admin->trashed()) {
            $admin->restore();
        }

        $admin->fill([
            'name'              => 'Admin',
            'email'             => $adminEmail,
            'password'          => Hash::make($adminPassword),
            'is_admin'          => true,
            'email_verified_at' => now(),
        ]);

        $admin->save();

        $this->command->info("Admin user ensured: {$adminEmail} (password set from ADMIN_PASSWORD env variable).");
        $this->command->warn("IMPORTANT: Change the admin password immediately after first login!");
    }
}
