<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CurrencySeeder::class);
        $this->call(CompanyTypesSeeder::class);
        $this->call(WorkTypeSeeder::class);
    }
}
