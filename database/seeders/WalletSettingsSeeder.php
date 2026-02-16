<?php

namespace Modules\Wallets\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class WalletSettingsSeeder extends Seeder
{
    /**
     * Seed the wallet settings.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'id_prefix', 'value' => 'W', 'type' => 'string'],
            ['key' => 'id_padding', 'value' => '8', 'type' => 'integer'],
            ['key' => 'number_prefix', 'value' => 'WLT', 'type' => 'string'],
            ['key' => 'number_separator', 'value' => '-', 'type' => 'string'],
            ['key' => 'number_date_format', 'value' => 'Ymd', 'type' => 'string'],
            ['key' => 'number_random_length', 'value' => '5', 'type' => 'integer'],
            ['key' => 'default_currency', 'value' => 'USD', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['group' => 'wallet', 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
