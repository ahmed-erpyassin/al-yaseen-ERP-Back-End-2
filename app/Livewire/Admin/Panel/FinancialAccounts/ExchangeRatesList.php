<?php

namespace App\Livewire\Admin\Panel\FinancialAccounts;

use Livewire\Component;
use App\Helpers\LivewireHelper;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class ExchangeRatesList extends Component
{
    use WithPagination;
    use LivewireHelper;

    protected $paginationTheme = 'bootstrap';
    public $pagination = 10;
    public $sort_field = 'id';
    public $sort_direction = 'asc';
    public $selectedExchangeRates = [];
    public $selectAll = false;
    public $search = "";
    public $filters = [];

    public function updatedSelectAll($value)
    {
        if ($value) {
            // حدد كل العناصر في الصفحة الحالية فقط
            $this->selectedExchangeRates = $this->getCurrentPageExchangeRatesIds();
        } else {
            $this->selectedExchangeRates = [];
        }
    }

    public function getCurrentPageExchangeRatesIds()
    {
        return $this->exchangeRates->slice(($this->currentPage - 1) * $this->pagination, $this->pagination)->pluck('id')->toArray();
    }

    public function loadExchangeRates()
    {
        $this->filters = [
            'search' => $this->search,
        ];

        $service = $this->setService('ExchangeRateService');
        return $service->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    #[Layout('layouts.admin.panel'), Title('Exchange Rates List')]
    public function render()
    {
        $exchangeRates = $this->loadExchangeRates();
        return view('livewire.admin.panel.financial-accounts.exchange-rates-list', compact('exchangeRates'));
    }

    public function edit($id)
    {
        // return redirect()->route('admin.panel.financial-accounts.exchange-rates.edit', ['id' => $id]);
    }
}
