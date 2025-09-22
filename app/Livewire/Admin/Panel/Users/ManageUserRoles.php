<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Users\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManageUserRoles extends Component
{
    use LivewireHelper;

    public $userId;
    public $user;

    public $guard_name; // سيتم تحديده حسب نوع المستخدم
    public $roles = [];
    public $permissionsByGroup = [];
    public $selectedRoles = [];
    public $selectedPermissions = [];

    public function mount()
    {
        $this->userId = session('user_id');
        $this->user = User::findOrFail($this->userId);

        // كل الأدوار من الحارسين
        $this->roles = Role::all();

        // الصلاحيات مجمعة حسب الحارس (api / web)
        $this->permissionsByGroup = [
            'api' => Permission::where('guard_name', 'api')->get()->groupBy('group')->toArray(),
            'web' => Permission::where('guard_name', 'web')->get()->groupBy('group')->toArray(),
        ];

        // الأدوار الحالية للمستخدم
        $this->selectedRoles = $this->user->roles->pluck('name')->toArray();

        // الصلاحيات الحالية للمستخدم
        $this->selectedPermissions = $this->user->permissions->pluck('name')->toArray();
    }

    public function update()
    {
        $data = $this->validate([
            'selectedRoles' => 'array',
            'selectedPermissions' => 'array',
        ]);

        // 1️⃣ تنظيف كل الأدوار والصلاحيات القديمة
        $this->user->roles()->detach();
        $this->user->permissions()->detach();

        // 2️⃣ تعيين الأدوار لكل guard
        foreach ($data['selectedRoles'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $this->user->assignRole($role);
            }
        }

        // 3️⃣ تعيين الصلاحيات لكل guard
        foreach ($data['selectedPermissions'] as $permName) {
            $perm = Permission::where('name', $permName)->first();
            if ($perm) {
                $this->user->givePermissionTo($perm);
            }
        }

        $this->alertMessage(__('Roles & Permissions updated successfully.'), 'success');
    }

    #[Layout('layouts.admin.panel'), Title('Manage User Roles & Permissions')]
    public function render()
    {
        return view('livewire.admin.panel.users.manage-user-roles', [
            'roles' => $this->roles,
            'permissionsByGroup' => $this->permissionsByGroup,
        ]);
    }
}
