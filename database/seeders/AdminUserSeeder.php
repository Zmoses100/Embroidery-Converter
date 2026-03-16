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

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => 'Admin',
                'email'             => $adminEmail,
                'password'          => Hash::make('password'),
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin user created: {$adminEmail} / password: password");
        $this->command->warn("IMPORTANT: Change the admin password immediately after first login!");
    }
}
