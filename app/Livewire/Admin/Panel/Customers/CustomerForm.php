<?php

namespace App\Livewire\Admin\Panel\Customers;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Customers\app\Services\CustomerService;
use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Models\Customer;

class CustomerForm extends Component
{
    use LivewireHelper;

    // Form fields
    public $company_id = '';
    public $branch_id = '';
    public $currency_id = '';
    public $employee_id = '';
    public $country_id = '';
    public $region_id = '';
    public $city_id = '';
    public $customer_number = '';
    public $company_name = '';
    public $first_name = '';
    public $second_name = '';
    public $contact_name = '';
    public $email = '';
    public $phone = '';
    public $mobile = '';
    public $address_one = '';
    public $address_two = '';
    public $postal_code = '';
    public $licensed_operator = '';
    public $tax_number = '';
    public $notes = '';
    public $status = 'active';
    public $code = '';
    public $invoice_type = '';
    public $category = '';

    // Alert properties
    public $showAlert = false;
    public $alertType = 'success';
    public $alertMessage = '';
    public $alertTitle = '';

    // Edit mode
    public $customerId = null;
    public $isEdit = false;

    protected function rules()
    {
        $rules = [
            'company_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'currency_id' => 'required|integer',
            'employee_id' => 'required|integer',
            'country_id' => 'required|integer',
            'region_id' => 'required|integer',
            'city_id' => 'required|integer',
            'company_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'second_name' => 'required|string|max:100',
            'contact_name' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'mobile' => 'required|string|max:50',
            'address_one' => 'required|string|max:255',
            'address_two' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'licensed_operator' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'invoice_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
        ];

        // Add unique validation rules based on edit mode
        if ($this->isEdit) {
            $rules['customer_number'] = 'required|string|max:50|unique:customers,customer_number,' . $this->customerId;
            $rules['email'] = 'required|email|max:150|unique:customers,email,' . $this->customerId;
            $rules['tax_number'] = 'required|string|max:50|unique:customers,tax_number,' . $this->customerId;
            $rules['code'] = 'required|string|max:50|unique:customers,code,' . $this->customerId;
        } else {
            $rules['customer_number'] = 'required|string|max:50|unique:customers,customer_number';
            $rules['email'] = 'required|email|max:150|unique:customers,email';
            $rules['tax_number'] = 'required|string|max:50|unique:customers,tax_number';
            $rules['code'] = 'required|string|max:50|unique:customers,code';
        }

        return $rules;
    }

    public function mount($customerId = null)
    {
        if ($customerId) {
            $this->customerId = $customerId;
            $this->isEdit = true;
            $this->loadCustomer();
        } else {
            // Set default values for create mode
            $this->status = 'active';
        }
    }

    public function loadCustomer()
    {
        try {
            $customer = Customer::findOrFail($this->customerId);
            
            $this->company_id = $customer->company_id ?? '';
            $this->branch_id = $customer->branch_id ?? '';
            $this->currency_id = $customer->currency_id ?? '';
            $this->employee_id = $customer->employee_id ?? '';
            $this->country_id = $customer->country_id ?? '';
            $this->region_id = $customer->region_id ?? '';
            $this->city_id = $customer->city_id ?? '';
            $this->customer_number = $customer->customer_number ?? '';
            $this->company_name = $customer->company_name ?? '';
            $this->first_name = $customer->first_name ?? '';
            $this->second_name = $customer->second_name ?? '';
            $this->contact_name = $customer->contact_name ?? '';
            $this->email = $customer->email ?? '';
            $this->phone = $customer->phone ?? '';
            $this->mobile = $customer->mobile ?? '';
            $this->address_one = $customer->address_one ?? '';
            $this->address_two = $customer->address_two ?? '';
            $this->postal_code = $customer->postal_code ?? '';
            $this->licensed_operator = $customer->licensed_operator ?? '';
            $this->tax_number = $customer->tax_number ?? '';
            $this->notes = $customer->notes ?? '';
            $this->status = $customer->status ?? 'active';
            $this->code = $customer->code ?? '';
            $this->invoice_type = $customer->invoice_type ?? '';
            $this->category = $customer->category ?? '';
        } catch (\Exception $e) {
            $this->showDivAlert('خطأ في تحميل بيانات العميل: ' . $e->getMessage(), 'error', 'خطأ');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $customerService = new CustomerService();
            
            // Create a mock request object with our data
            $request = new \Illuminate\Http\Request();
            $request->merge($this->getFormData());
            $request->setUserResolver(function () {
                return auth()->user();
            });

            if ($this->isEdit) {
                $customer = Customer::findOrFail($this->customerId);
                $customerService->update($request, $customer);
                $this->showDivAlert('تم تحديث بيانات العميل بنجاح', 'success', 'تم بنجاح');
            } else {
                $customerService->store($request);
                $this->showDivAlert('تم إنشاء العميل بنجاح', 'success', 'تم بنجاح');
                $this->resetForm();
            }

        } catch (\Exception $e) {
            $this->showDivAlert('حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage(), 'error', 'خطأ');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'company_id', 'branch_id', 'currency_id', 'employee_id',
            'country_id', 'region_id', 'city_id', 'customer_number',
            'company_name', 'first_name', 'second_name', 'contact_name',
            'email', 'phone', 'mobile', 'address_one', 'address_two',
            'postal_code', 'licensed_operator', 'tax_number', 'notes',
            'status', 'code', 'invoice_type', 'category'
        ]);
        $this->status = 'active';
    }

    public function cancel()
    {
        if ($this->isEdit) {
            return redirect()->route('admin.panel.customers.index');
        }
        $this->resetForm();
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

    private function getFormData()
    {
        return [
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'currency_id' => $this->currency_id,
            'employee_id' => $this->employee_id,
            'country_id' => $this->country_id,
            'region_id' => $this->region_id,
            'city_id' => $this->city_id,
            'customer_number' => $this->customer_number,
            'company_name' => $this->company_name,
            'first_name' => $this->first_name,
            'second_name' => $this->second_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address_one' => $this->address_one,
            'address_two' => $this->address_two,
            'postal_code' => $this->postal_code,
            'licensed_operator' => $this->licensed_operator,
            'tax_number' => $this->tax_number,
            'notes' => $this->notes,
            'status' => $this->status,
            'code' => $this->code,
            'invoice_type' => $this->invoice_type,
            'category' => $this->category,
        ];
    }

    #[Layout('layouts.admin.panel'), Title('إدارة العملاء')]
    public function render()
    {
        return view('livewire.admin.panel.customers.customer-form');
    }
}
