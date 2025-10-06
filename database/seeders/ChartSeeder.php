<?php

namespace Database\Seeders;

use App\Models\Chart;
use Illuminate\Database\Seeder;

class ChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Pie Chart',
            'Bar Chart',
            'Line Chart',
            'Area Chart',
            'Scatter Chart',
            'Doughnut Chart',
            'Radar Chart',
            'Bubble Chart',
            'Polar Area Chart',
            'Grid',
        ];

        foreach ($names as $name) {
            Chart::firstOrCreate(['name' => $name]);
        }
    }
}


