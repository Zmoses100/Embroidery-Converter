<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                 => 'Free',
                'slug'                 => 'free',
                'description'          => 'Perfect for trying out Embroidery Converter.',
                'price_monthly'        => 0,
                'price_yearly'         => 0,
                'conversions_per_day'  => 5,
                'storage_limit_mb'     => 100,
                'max_file_size_mb'     => 5,
                'max_batch_size'       => 1,
                'preview_enabled'      => false,
                'history_enabled'      => true,
                'api_access'           => false,
                'priority_queue'       => false,
                'is_active'            => true,
                'is_featured'          => false,
                'sort_order'           => 1,
                'features' => [
                    '5 conversions per day',
                    '100 MB storage',
                    'All formats supported',
                    'Conversion history',
                ],
            ],
            [
                'name'                 => 'Pro',
                'slug'                 => 'pro',
                'description'          => 'For hobbyists and small embroidery businesses.',
                'price_monthly'        => 9.99,
                'price_yearly'         => 99.00,
                'conversions_per_day'  => 100,
                'storage_limit_mb'     => 1024,
                'max_file_size_mb'     => 25,
                'max_batch_size'       => 10,
                'preview_enabled'      => true,
                'history_enabled'      => true,
                'api_access'           => false,
                'priority_queue'       => false,
                'is_active'            => true,
                'is_featured'          => true,
                'sort_order'           => 2,
                'features' => [
                    '100 conversions per day',
                    '1 GB storage',
                    'Batch conversion (10 files)',
                    'Design preview',
                    'Priority support',
                ],
            ],
            [
                'name'                 => 'Business',
                'slug'                 => 'business',
                'description'          => 'For professional embroidery shops and teams.',
                'price_monthly'        => 29.99,
                'price_yearly'         => 299.00,
                'conversions_per_day'  => -1, // Unlimited
                'storage_limit_mb'     => 10240,
                'max_file_size_mb'     => 50,
                'max_batch_size'       => 50,
                'preview_enabled'      => true,
                'history_enabled'      => true,
                'api_access'           => true,
                'priority_queue'       => true,
                'is_active'            => true,
                'is_featured'          => false,
                'sort_order'           => 3,
                'features' => [
                    'Unlimited conversions',
                    '10 GB storage',
                    'Batch conversion (50 files)',
                    'Design preview',
                    'API access',
                    'Priority queue',
                    'Dedicated support',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
