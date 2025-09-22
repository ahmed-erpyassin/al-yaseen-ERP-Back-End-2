<?php

namespace App\Livewire\Admin\Panel\Companies;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Companies\Models\Company;

class ShowCompany extends Component
{
    public $companyId;
    public $company;

    public function mount()
    {
        $this->companyId = session('company_id');

        $this->company = Company::with([
            'user',
            'currency',
            'fiscalYear',
            'industry',
            'businessType',
            'country',
            'region',
            'city',
            'branches'
        ])->findOrFail($this->companyId);
    }

    #[Layout('layouts.admin.panel'), Title('Company Details')]
    public function render()
    {
        return view('livewire.admin.panel.companies.show-company');
    }
}
