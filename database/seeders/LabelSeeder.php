<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            ['name' => 'Fiber Cut',    'color' => '#ef4444'],
            ['name' => 'ONU Config',   'color' => '#f97316'],
            ['name' => 'Speed Slow',   'color' => '#eab308'],
            ['name' => 'Routing',      'color' => '#3b82f6'],
            ['name' => 'IPTV',         'color' => '#8b5cf6'],
            ['name' => 'VIP Customer', 'color' => '#10b981'],
        ];

        foreach ($labels as $label) {
            Label::firstOrCreate(['name' => $label['name']], ['color' => $label['color']]);
        }
    }
}
