<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\Services\PermissionManagerService;

class PermissionRouteSeeder extends Seeder
{
    public function run(): void
    {
        $manager = new PermissionManagerService();
        $count = $manager->sync();

        $this->command->info("✅ تم توليد {$count} صلاحية من راوترات الـ API.");
    }
}
