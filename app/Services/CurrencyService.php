<?php

namespace App\Services;

use Modules\FinancialAccounts\Models\Currency;

class CurrencyService
{
    public $model = Currency::class;

    public function __construct()
    {
        $this->model = new Currency();
    }

    public function model($id)
    {
        return Currency::find($id);
    }

    public function data($filters, $sort_field, $sort_direction, $paginate = 10)
    {
        return Currency::data()
            ->filters($filters)
            ->reorder($sort_field, $sort_direction)
            ->paginate($paginate);
    }

    public function changeAccountStatus($id)
    {
        return Currency::changeAccountStatus($id);
    }

    public function delete($id)
    {
        return Currency::deleteModel($id);
    }

    public function store($data)
    {
        return Currency::store($data);
    }

    public function update($data, $id)
    {
        return Currency::updateModel($data, $id);
    }
}
