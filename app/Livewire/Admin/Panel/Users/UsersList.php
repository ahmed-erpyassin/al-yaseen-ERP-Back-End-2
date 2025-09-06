<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

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

    #[Layout('layouts.admin.panel'), Title('Users List')]
    public function render()
    {
        $this->filters = [
            'search' => $this->search,
        ];

        $service = $this->setService('UserService');
        $users = $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);

        return view('livewire.admin.panel.users.users-list', compact('users'));
    }

    public function edit($id)
    {
        session(['user_id' => $id]);
        // return redirect()->route('sales.orders.edit');
    }
}
