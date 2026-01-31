<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Enums\QuoteStatus;
use App\Enums\SalesTerm;

class ErpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla si existe
        DB::connection('erp_db')->table('quotes')->truncate();
        DB::connection('erp_db')->table('customers')->truncate();
        DB::connection('erp_db')->table('bills_work_orders')->truncate();

        // Seed Customers
        DB::connection('erp_db')->table('customers')->insert([
            ['id' => 1, 'Empresa' => 'Empresa Demo 1'],
            ['id' => 2, 'Empresa' => 'Cliente Test 2'],
            ['id' => 3, 'Empresa' => 'Corporación 3'],
        ]);

        $quotes = [
            [
                'id' => 'COT001',
                'Cliente' => 1,
                'Total' => 1500.00,
                'Date' => now()->subDays(2)->format('Y-m-d'),
                'SalesTerm' => SalesTerm::CREDIT->value,
                'Status' => QuoteStatus::APPROVED->value,
            ],
            [
                'id' => 'COT002',
                'Cliente' => 2,
                'Total' => 2500.50,
                'Date' => now()->subDays(12)->format('Y-m-d'),
                'SalesTerm' => SalesTerm::COD->value,
                'Status' => QuoteStatus::PENDING->value,
            ],
            [
                'id' => 'COT003',
                'Cliente' => 1,
                'Total' => 500.00,
                'Date' => now()->subDays(20)->format('Y-m-d'),
                'SalesTerm' => SalesTerm::CREDIT_COD->value,
                'Status' => QuoteStatus::EXPIRED->value,
            ],
            [
                'id' => 'COT004',
                'Cliente' => 3,
                'Total' => 10000.00,
                'Date' => now()->format('Y-m-d'),
                'SalesTerm' => SalesTerm::CREDIT->value,
                'Status' => QuoteStatus::ACTIVE->value,
            ]
        ];

        DB::connection('erp_db')->table('quotes')->insert($quotes);

        // Seed WorkOrders (linked to COT004 to verify dependency)
        // COT001, COT002, COT003 won't have WO, so they should appear in "Pending without WO" logic if applicable
        DB::connection('erp_db')->table('bills_work_orders')->insert([
            ['id' => 'WO001', 'Invoice' => 'COT004', 'Date' => now()->format('Y-m-d')],
        ]);
    }
}
