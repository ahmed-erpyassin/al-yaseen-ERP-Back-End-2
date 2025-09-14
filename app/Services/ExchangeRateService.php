<?php

namespace App\Services;

use Modules\FinancialAccounts\Models\ExchangeRate;

class ExchangeRateService
{
    public $model = ExchangeRate::class;

    public function __construct()
    {
        $this->model = new ExchangeRate();
    }

    public function model($id)
    {
        return ExchangeRate::find($id);
    }

    public function data($filters, $sort_field, $sort_direction, $paginate = 10)
    {
        return ExchangeRate::data()
            ->filters($filters)
            ->reorder($sort_field, $sort_direction)
            ->paginate($paginate);
    }

    public function changeAccountStatus($id)
    {
        return ExchangeRate::changeAccountStatus($id);
    }

    public function delete($id)
    {
        return ExchangeRate::deleteModel($id);
    }

    public function store($data)
    {
        return ExchangeRate::store($data);
    }

    public function update($data, $id)
    {
        return ExchangeRate::updateModel($data, $id);
    }
}
