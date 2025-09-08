<?php

namespace App\Livewire\Admin\Panel\Companies;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class CompaniesList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';

    public $pagination = 10;
    public $sort_field = 'id';
    public $sort_direction = 'asc';

    public $selectedCompanies = [];
    public $selectAll = false;

    public $search = "";
    public $filters = [];

    public function updatedSelectAll($value)
    {
        if ($value) {
            // حدد كل العناصر في الصفحة الحالية فقط
            $this->selectedCompanies = $this->getCurrentPageCompaniesIds();
        } else {
            $this->selectedCompanies = [];
        }
    }

    public function getCurrentPageCompaniesIds()
    {
        return $this->companies->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadCompanies()
    {
        $this->filters = [
            'search' => $this->search,
        ];
        $service = $this->setService('CompanyService');
        return $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    #[Layout('layouts.admin.panel'), Title('Companies List')]
    public function render()
    {
        $companies = $this->loadCompanies();
        return view('livewire.admin.panel.companies.companies-list', compact('companies'));
    }
}
