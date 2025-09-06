<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\ExchangeRate;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExchangeRate::create([
            'currency_id' => 1, // USD
            'rate_date' => now()->toDateString(),
            'rate' => 3.65,
            'user_id' => 1,
            'company_id' => 1,
            'branch_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
