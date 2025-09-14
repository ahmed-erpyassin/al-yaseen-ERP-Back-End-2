<?php

namespace App\Services;

use Modules\FinancialAccounts\Models\FiscalYear;

class FiscalYearService
{
    public $model = FiscalYear::class;

    public function __construct()
    {
        $this->model = new FiscalYear();
    }

    public function model($id)
    {
        return FiscalYear::find($id);
    }

    public function data($filters, $sort_field, $sort_direction, $paginate = 10)
    {
        return FiscalYear::data()
            ->filters($filters)
            ->reorder($sort_field, $sort_direction)
            ->paginate($paginate);
    }

    public function changeAccountStatus($id)
    {
        return FiscalYear::changeAccountStatus($id);
    }

    public function delete($id)
    {
        return FiscalYear::deleteModel($id);
    }

    public function store($data)
    {
        return FiscalYear::store($data);
    }

    public function update($data, $id)
    {
        return FiscalYear::updateModel($data, $id);
    }
}
