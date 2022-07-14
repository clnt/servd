<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create(['key' => Setting::KEY_PHP_VERSION, 'value' => '7.4']);
        Setting::create(['key' => Setting::KEY_WORKING_DIRECTORY, 'value' => null]);
        Setting::create(['key' => Setting::KEY_TIMEZONE, 'value' => 'UTC']);
    }
}
