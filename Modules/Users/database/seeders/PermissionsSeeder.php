<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'users.index',   'group' => 'Users', 'label' => 'Show Users'],
            ['name' => 'users.store',   'group' => 'Users', 'label' => 'Create User'],
            ['name' => 'users.update',  'group' => 'Users', 'label' => 'Update User'],
            ['name' => 'users.destroy', 'group' => 'Users', 'label' => 'Delete User'],
            ['name' => 'users.show',    'group' => 'Users', 'label' => 'Show User Details'],
        ];


        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => 'api.' . $perm['name']], [
                'group' => $perm['group'],
                'label' => $perm['label'],
                'guard_name' => 'api', // 🔥 مهم
            ]);
        }

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => 'web.' . $perm['name']], [
                'group' => $perm['group'],
                'label' => $perm['label'],
                'guard_name' => 'web', // 🔥 مهم
            ]);
        }
    }
}
