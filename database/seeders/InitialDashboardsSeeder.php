<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialDashboardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logistica = \App\Models\Dashboard::firstOrCreate(
            ['slug' => 'logistica'],
            ['title' => 'Logística', 'icon' => 'heroicon-o-truck']
        );

        $operaciones = \App\Models\Dashboard::firstOrCreate(
            ['slug' => 'operaciones'],
            ['title' => 'Operaciones', 'icon' => 'heroicon-o-cpu-chip']
        );

        // Migrar widgets existentes
        \App\Models\CustomWidget::where('section', 'logistica')->update(['dashboard_id' => $logistica->id]);
        \App\Models\CustomWidget::where('section', 'operaciones')->update(['dashboard_id' => $operaciones->id]);
    }
}
