<?php

namespace App\Livewire\Admin\Panel\Customers;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Customers\app\Services\CustomerService;
use Modules\Customers\Models\Customer;

class CustomersList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';

    public $pagination = 10;
    public $sort_field = 'created_at';
    public $sort_direction = 'desc';

    public $selectedCustomers = [];
    public $selectAll = false;

    public $search = "";
    public $filters = [];

    // Alert properties
    public $showAlert = false;
    public $alertType = 'success';
    public $alertMessage = '';
    public $alertTitle = '';

    // Delete confirmation
    public $showDeleteConfirm = false;
    public $customerToDelete = null;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = $this->getCurrentPageCustomersIds();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function getCurrentPageCustomersIds()
    {
        return $this->customers->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadCustomers()
    {
        $this->filters = [
            'customerSearch' => $this->search,
            'sort_by' => $this->sort_field,
            'sort_order' => $this->sort_direction,
        ];

        $service = new CustomerService();
        return $service->getCustomersWithPagination(request()->merge($this->filters), $this->pagination);
    }

    public function edit($id)
    {
        return redirect()->route('admin.panel.customers.edit', ['customerId' => $id]);
    }

    public function delete($id)
    {
        $this->customerToDelete = $id;
        $this->showDeleteConfirm = true;
    }

    public function confirmDelete()
    {
        try {
            $customer = Customer::findOrFail($this->customerToDelete);
            $service = new CustomerService();
            $service->destroy($customer, auth()->id());
            
            $this->showDivAlert('تم حذف العميل بنجاح', 'success', 'تم بنجاح');
            $this->showDeleteConfirm = false;
            $this->customerToDelete = null;
            
        } catch (\Exception $e) {
            $this->showDivAlert('حدث خطأ أثناء حذف العميل: ' . $e->getMessage(), 'error', 'خطأ');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirm = false;
        $this->customerToDelete = null;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedCustomers)) {
            $this->showDivAlert('يرجى اختيار العملاء المراد حذفهم', 'warning', 'تحذير');
            return;
        }

        try {
            $service = new CustomerService();
            $service->bulkDelete($this->selectedCustomers, auth()->id());
            
            $this->showDivAlert('تم حذف ' . count($this->selectedCustomers) . ' عميل بنجاح', 'success', 'تم بنجاح');
            $this->selectedCustomers = [];
            $this->selectAll = false;
            
        } catch (\Exception $e) {
            $this->showDivAlert('حدث خطأ أثناء حذف العملاء: ' . $e->getMessage(), 'error', 'خطأ');
        }
    }

    public function showDivAlert($message, $type = 'success', $title = '')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
        $this->alertTitle = $title;
        $this->showAlert = true;
    }

    public function hideAlert()
    {
        $this->showAlert = false;
    }

    public function sortBy($field)
    {
        if ($this->sort_field === $field) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_field = $field;
            $this->sort_direction = 'asc';
        }
    }

    #[Layout('layouts.admin.panel'), Title('قائمة العملاء')]
    public function render()
    {
        $customers = $this->loadCustomers();
        return view('livewire.admin.panel.customers.customers-list', compact('customers'));
    }
}

