<?php

namespace App\Livewire\Admin\Panel\Roles;

use App\Helpers\LivewireHelper;
use Livewire\Component;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class RolesList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';

    public $pagination = 10;
    public $sort_field = 'id';
    public $sort_direction = 'asc';
    public $search = "";

    public $guard = "";
    public $filters = [];
    public $selectedRoles = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            // حدد كل العناصر في الصفحة الحالية فقط
            $this->selectedRoles = $this->getCurrentPageRolesIds();
        } else {
            $this->selectedRoles = [];
        }
    }

    public function getCurrentPageRolesIds()
    {
        return $this->roles->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadRoles()
    {
        $this->filters = [
            'search' => $this->search,
            'guard' => $this->guard,
        ];

        $service = $this->setService('RoleService');
        return $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    #[Layout('layouts.admin.panel'), Title('Roles List')]
    public function render()
    {
        $roles = $this->loadRoles();
        return view('livewire.admin.panel.roles.roles-list', compact('roles'));
    }

    public function edit($id)
    {
        session(['role_id' => $id]);
        return redirect()->route('admin.panel.users.roles.edit', ['lang' => app()->getLocale()]);
    }

    public function create()
    {
        return redirect()->route('admin.panel.users.roles.create', ['lang' => app()->getLocale()]);
    }
}
