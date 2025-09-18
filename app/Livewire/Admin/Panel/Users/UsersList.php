<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Livewire\Component;

class UsersList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';

    public $pagination = 10;
    public $sort_field = 'id';
    public $sort_direction = 'asc';

    public $selectedUsers = [];
    public $selectAll = false;

    public $search = "";
    public $filters = [];

    public function updatedSelectAll($value)
    {
        if ($value) {
            // حدد كل العناصر في الصفحة الحالية فقط
            $this->selectedUsers = $this->getCurrentPageUsersIds();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function getCurrentPageUsersIds()
    {
        return $this->users->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadUsers()
    {
        $this->filters = [
            'search' => $this->search,
        ];

        $service = $this->setService('UserService');
        return $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    #[Layout('layouts.admin.panel'), Title('Users List')]
    public function render()
    {
        $users = $this->loadUsers();
        return view('livewire.admin.panel.users.users-list', compact('users'));
    }

    public function edit($id)
    {
        session(['user_id' => $id]);
        // return redirect()->route('sales.orders.edit');
    }

    public function manageUserRoles($id)
    {
        session(['user_id' => $id]);
        return redirect()->route('admin.panel.users.manage-user-roles');
    }
}
