<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Companies\Models\Company;
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
            'first_name'        => 'مدير',
            'second_name'       => 'النظام',
            'phone'             => '0599916672',
            'password'          => Hash::make('admin123'),
            'status'            => 'active',
            'type'              => 'super_admin',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'created_by'        => 1,
            'updated_by'        => 1,
        ]);

        // إنشاء دوري المشرف الأعلى (واحد web وواحد api)
        $roles = [];
        foreach (['web', 'api'] as $guard) {
            $roles[$guard] = Role::firstOrCreate([
                'name'       => 'super_admin',
                'guard_name' => $guard,
            ]);
        }

        // ربط كل الصلاحيات المتوفرة بكل دور حسب الـ guard
        foreach ($roles as $guard => $role) {
            $permissions = Permission::where('guard_name', $guard)->pluck('name')->toArray();
            $role->syncPermissions($permissions);
        }

        // ربط المستخدم بالدورين (web + api)
        $user->syncRoles([
            $roles['web']->name,
            $roles['api']->name,
        ]);

        $company = Company::firstOrCreate([
            'user_id'                               => $user->id,
            'title'                                 => 'Yassin ERP Company',
            'commercial_registeration_number'       => 'YERPC-001',
            'address'                               => 'رام الله، فلسطين',
            'logo'                                  => 'path/to/logo.png',
            'email'                                 => 'info@yassincompany.com',
            'landline'                              => '02-1234567',
            'mobile'                                => '0599916672',
            'income_tax_rate'                       => 15.00,
            'vat_rate'                              => 16.00,
            'status'                                => 'active',
            'created_by'                            => $user->id,
            'updated_by'                            => $user->id,
        ]);

        $this->command->info('✅ تم إنشاء مدير النظام وربطه بكل الصلاحيات (web + api).');
    }
}
