<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\FiscalYear;

class FiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          FiscalYear::create([
            'name' => 'FY 2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'open',
            'user_id' => 1,
            'company_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
