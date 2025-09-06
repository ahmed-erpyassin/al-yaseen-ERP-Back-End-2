<?php

namespace Modules\FinancialAccounts\Database\Seeders;

use Illuminate\Database\Seeder;

class FinancialAccountsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            ExchangeRateSeeder::class,
            FiscalYearSeeder::class,
        ]);
    }
}
