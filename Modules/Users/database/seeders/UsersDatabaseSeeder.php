<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Contracts\Permission;

class UsersDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            PermissionRouteSeeder::class,
            ModuleAccessPermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
