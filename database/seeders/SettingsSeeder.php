<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General
            ['key' => 'app_name',                'value' => 'Embroidery Converter', 'group' => 'general', 'type' => 'string'],
            ['key' => 'app_description',         'value' => 'Convert embroidery files online', 'group' => 'general', 'type' => 'string'],
            ['key' => 'support_email',           'value' => 'support@example.com', 'group' => 'general', 'type' => 'string'],
            ['key' => 'maintenance_mode',        'value' => '0', 'group' => 'general', 'type' => 'boolean'],

            // Conversions
            ['key' => 'max_upload_size_mb',      'value' => '50', 'group' => 'conversions', 'type' => 'integer'],
            ['key' => 'conversion_timeout_sec',  'value' => '300', 'group' => 'conversions', 'type' => 'integer'],
            ['key' => 'enable_preview',          'value' => '0', 'group' => 'conversions', 'type' => 'boolean'],

            // Email
            ['key' => 'send_conversion_emails',  'value' => '1', 'group' => 'email', 'type' => 'boolean'],
            ['key' => 'send_welcome_email',       'value' => '1', 'group' => 'email', 'type' => 'boolean'],

            // Storage
            ['key' => 'auto_delete_converted_days', 'value' => '30', 'group' => 'storage', 'type' => 'integer'],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
