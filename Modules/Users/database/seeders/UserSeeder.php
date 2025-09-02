<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء المستخدم الرئيسي
        $user = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'first_name' => 'مدير',
            'second_name' => 'النظام',
            'phone' => '0599916672',
            'password' => Hash::make('admin123'),
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // إنشاء دور المشرف الأعلى
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'api', // 🔥 مهم
        ]);

        // ربط كل الصلاحيات المتوفرة بالدور
        $permissions = Permission::pluck('name')->toArray();
        $role->syncPermissions($permissions);

        // ربط الدور بالمستخدم
        $user->syncRoles([$role->name]);

        $this->command->info('✅ تم إنشاء مدير النظام وربطه بكل الصلاحيات.');
    }
}
