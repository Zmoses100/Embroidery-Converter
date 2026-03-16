<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlansSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
