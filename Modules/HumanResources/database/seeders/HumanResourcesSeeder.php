<?php

namespace Modules\HumanResources\Database\Seeders;

use Illuminate\Database\Seeder;

class HumanResourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Human Resources module seeding...');

        $this->call([
            CompleteHRSeeder::class,
            PayrollDataSeeder::class,
        ]);

        $this->command->info('✅ Human Resources module seeding completed successfully!');
    }
}
