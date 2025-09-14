<?php

namespace App\Livewire\Admin\Panel\FinancialAccounts;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Livewire\Component;

class FiscalYearsList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';
    public $pagination = 10;
    public $sort_field = 'id';
    public $sort_direction = 'asc';

    public $selectedFiscalYears = [];
    public $selectAll = false;

    public $search = "";
    public $filters = [];

    public function updatedSelectAll($value)
    {
        if ($value) {
            // حدد كل العناصر في الصفحة الحالية فقط
            $this->selectedFiscalYears = $this->getCurrentPageFiscalYearsIds();
        } else {
            $this->selectedFiscalYears = [];
        }
    }

    public function getCurrentPageFiscalYearsIds()
    {
        return $this->fiscalYears->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadFiscalYears()
    {
        $this->filters = [
            'search' => $this->search,
        ];

        $service = $this->setService('FiscalYearService');
        return $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    #[Layout('layouts.admin.panel'), Title('Fiscal Years List')]
    public function render()
    {
        $fiscalYears = $this->loadFiscalYears();
        return view('livewire.admin.panel.financial-accounts.fiscal-years-list', compact('fiscalYears'));
    }

    public function edit($id)
    {
        // return redirect()->route('admin.panel.financial-accounts.fiscal-years.edit', ['id' => $id]);
    }
}
