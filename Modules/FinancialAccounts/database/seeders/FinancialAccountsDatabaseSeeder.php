<?php

namespace Modules\FinancialAccounts\database\seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialAccounts\Database\Seeders\FinancialAccountsCoreSeeder;
use Modules\FinancialAccounts\Database\Seeders\TaxRateSeeder;
use Modules\FinancialAccounts\Database\Seeders\AccountGroupSeeder;
use Modules\FinancialAccounts\Database\Seeders\AccountSeeder;
use Modules\FinancialAccounts\Database\Seeders\JournalsFinancialSeeder;
use Modules\FinancialAccounts\Database\Seeders\JournalEntrySeeder;

class FinancialAccountsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FinancialAccountsCoreSeeder::class,
            AccountGroupSeeder::class,
            AccountSeeder::class,
            JournalsFinancialSeeder::class,
            JournalEntrySeeder::class,
            // TaxRateSeeder::class, // Commented out due to enum issue
        ]);
    }
}
