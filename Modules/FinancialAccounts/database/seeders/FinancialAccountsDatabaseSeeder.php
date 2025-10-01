<?php

namespace Modules\FinancialAccounts\database\seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Database\Seeders\FinancialAccountsCoreSeeder;
use Modules\FinancialAccounts\Database\Seeders\TaxRateSeeder;

class FinancialAccountsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FinancialAccountsCoreSeeder::class,
            TaxRateSeeder::class,
        ]);
    }
}
