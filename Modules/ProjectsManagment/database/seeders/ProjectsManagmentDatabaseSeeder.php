<?php

namespace Modules\ProjectsManagment\Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectsManagmentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding Projects Management Module...');

        $this->call([
            ProjectSeeder::class,
            ProjectMilestoneSeeder::class,
            ProjectTaskSeeder::class,
            ProjectResourceSeeder::class,
            ProjectFinancialSeeder::class,
            ProjectRiskSeeder::class,
            ProjectDocumentSeeder::class,
        ]);

        $this->command->info('✅ Projects Management Module seeded successfully!');
    }
}
