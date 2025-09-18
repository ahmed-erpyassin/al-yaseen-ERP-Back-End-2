<?php

namespace App\Livewire\Admin\Panel\Roles;

use App\Helpers\LivewireHelper;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class EditRole extends Component
{
    use LivewireHelper;

    public $roleId = null;
    public $name = '';
    public $guard_name = 'web'; // القيمة الافتراضية
    public $selectedPermissions = []; // الصلاحيات المختارة
    public $permissionsByGroup = [];  // لتقسيم الصلاحيات حسب المجموعة

    protected function rules()
    {
        return [
            'guard_name' => 'required|in:web,api',
            'selectedPermissions' => 'array',
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('roles')
                    ->where(fn($query) => $query->where('guard_name', $this->guard_name))
                    ->ignore($this->roleId),
            ]
        ];
    }

    public function mount()
    {
        $roleId = session('role_id');
        $this->roleId = $roleId;

        // جلب جميع الصلاحيات وتقسيمها حسب المجموعة
        $this->permissionsByGroup = Permission::all()->groupBy('group')->toArray();

        if ($roleId) {
            $this->loadRole($roleId);
        }
    }

    public function loadRole($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    #[Layout('layouts.admin.panel'), Title('Edit Role')]
    public function render()
    {
        return view('livewire.admin.panel.roles.edit-role', [
            'permissionsByGroup' => $this->permissionsByGroup
        ]);
    }

    public function update()
    {
        $data = $this->validate();

        $service = $this->setService('RoleService');
        $role = $service->update($data, $this->roleId);

        if ($role) {
            // تحديث الصلاحيات المرتبطة بالدور
            $role->syncPermissions($this->selectedPermissions);

            $this->alertMessage(__('Role updated successfully.'), 'success');
            return redirect()->route('admin.panel.users.roles.list', ['lang' => app()->getLocale()]);
        } else {
            $this->alertMessage(__('An error occurred while updating the role. Please try again.'), 'error');
        }
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'guard_name') {
            // إعادة تعيين الصلاحيات المختارة عند تغيير الـ guard
            $this->selectedPermissions = [];
        }
    }
}
