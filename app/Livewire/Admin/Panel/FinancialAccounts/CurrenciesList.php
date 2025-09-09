<?php

namespace App\Livewire\Admin\Panel\FinancialAccounts;

use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class CurrenciesList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';
    public $pagination = 10;
    public $sort_field = 'id';
    public $sort_direction = 'asc';

    public $selectedCurrencies = [];
    public $selectAll = false;

    public $search = "";
    public $filters = [];

    public function updatedSelectAll($value)
    {
        if ($value) {
            // حدد كل العناصر في الصفحة الحالية فقط
            $this->selectedCurrencies = $this->getCurrentPageCurrenciesIds();
        } else {
            $this->selectedCurrencies = [];
        }
    }

    public function getCurrentPageCurrenciesIds()
    {
        return $this->currencies->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadCurrencies()
    {
        $this->filters = [
            'search' => $this->search,
        ];

        $service = $this->setService('CurrencyService');
        return $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    #[Layout('layouts.admin.panel'), Title('Currencies List')]
    public function render()
    {
        $currencies = $this->loadCurrencies();
        return view('livewire.admin.panel.financial-accounts.currencies-list', compact('currencies'));
    }

    public function edit($id)
    {
        // return redirect()->route('admin.panel.financial-accounts.currencies.edit', ['id' => $id]);
    }
}
