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

        // تحديد guard حسب نوع المستخدم
        $this->guard_name = match ($this->user->type) {
            'customer' => 'api',
            default => 'web', // super_admin & admin
        };

        // جلب الأدوار حسب guard
        $this->roles = Role::where('guard_name', $this->guard_name)->get();

        // جلب الصلاحيات حسب guard مجمعة حسب المجموعة
        $this->permissionsByGroup = Permission::where('guard_name', $this->guard_name)
            ->get()
            ->groupBy('group')
            ->toArray();

        // تعيين القيم الحالية للمستخدم
        $this->selectedRoles = $this->user->roles->pluck('name')->toArray();
        $this->selectedPermissions = $this->user->permissions->pluck('name')->toArray();
    }

    public function update()
    {
        $data = $this->validate([
            'selectedRoles' => 'array',
            'selectedPermissions' => 'array',
        ]);

        // تحديث الأدوار للمستخدم
        $this->user->syncRoles($data['selectedRoles']);
        $this->user->syncPermissions($data['selectedPermissions']);

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
