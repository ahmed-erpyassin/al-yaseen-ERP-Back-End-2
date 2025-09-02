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
        // صلاحيات إضافية لليوزر
        $permissions = [
            ['name' => 'view_users', 'group' => 'Users', 'label' => 'عرض المستخدمين'],
            ['name' => 'create_users', 'group' => 'Users', 'label' => 'إنشاء مستخدم'],
            ['name' => 'edit_users', 'group' => 'Users', 'label' => 'تعديل مستخدم'],
            ['name' => 'delete_users', 'group' => 'Users', 'label' => 'حذف مستخدم'],
            ['name' => 'edit_user_permissions', 'group' => 'Users', 'label' => 'إدارة صلاحيات المستخدم'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], [
                'group' => $perm['group'],
                'label' => $perm['label'],
                'guard_name' => 'api', // 🔥 مهم
            ]);
        }
    }
}
