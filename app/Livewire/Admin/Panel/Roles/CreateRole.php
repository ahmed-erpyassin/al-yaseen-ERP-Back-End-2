<?php

namespace App\Livewire\Admin\Panel\Roles;

use App\Helpers\LivewireHelper;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Permission;

class CreateRole extends Component
{
    use LivewireHelper;

    public $name;
    public $guard_name = 'web'; // القيمة الافتراضية
    public $selectedPermissions = []; // مصفوفة الصلاحيات المختارة
    public $permissionsByGroup = []; // لتقسيم الصلاحيات حسب المجموعة

    protected $rules = [
        'name'       => 'required|string|min:3|max:50|unique:roles,name',
        'guard_name' => 'required|in:web,api',
        'selectedPermissions' => 'required|array|min:1',
    ];

    public function mount()
    {
        // جلب جميع الصلاحيات وتقسيمها حسب المجموعة
        $this->permissionsByGroup = Permission::all()->groupBy('group')->toArray();
    }

    #[Layout('layouts.admin.panel'), Title('Create Role')]
    public function render()
    {
        return view('livewire.admin.panel.roles.create-role', [
            'permissionsByGroup' => $this->permissionsByGroup
        ]);
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'guard_name') {
            // إعادة تعيين الصلاحيات المختارة عند تغيير الـ guard
            $this->selectedPermissions = [];
        }
    }

    public function create()
    {
        $data = $this->validate();

        $service = $this->setService('RoleService');
        $role = $service->store($data);

        if ($role) {
            // ربط الصلاحيات بالدور
            $role->syncPermissions($this->selectedPermissions);

            $this->alertMessage(__('Role created successfully.'), 'success');
            return redirect()->route('admin.panel.users.roles.list', ['lang' => app()->getLocale()]);
        } else {
            $this->alertMessage(__('An error occurred while creating the role. Please try again.'), 'error');
        }
    }
}
