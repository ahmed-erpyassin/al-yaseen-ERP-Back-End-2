<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
            'user_id' => 1,
            'company_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimal_places' => 2,
            'user_id' => 1,
            'company_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
