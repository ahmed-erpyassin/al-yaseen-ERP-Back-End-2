<?php

namespace Modules\Purchases\Database\Seeders;

use Illuminate\Database\Seeder;

class PurchasesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Purchases Module Seeding...');

        $this->call([
            PurchaseSeeder::class,
            PurchaseItemSeeder::class,
        ]);

        $this->command->info('✅ Purchases Module Seeding Completed!');
    }
}
