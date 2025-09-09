<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Companies\Models\Company;

class CompanyService
{
    public $model = Company::class;

    public function __construct()
    {
        $this->model = new Company();
    }

    public function model($id)
    {
        return Company::find($id);
    }

    public function data($filters, $sort_field, $sort_direction, $paginate = 10)
    {
        return Company::data()
            ->filters($filters)
            ->reorder($sort_field, $sort_direction)
            ->paginate($paginate);
    }

    public function changeAccountStatus($id)
    {
        return Company::changeAccountStatus($id);
    }

    public function delete($id)
    {
        return Company::deleteModel($id);
    }

    public function store($data)
    {
        return Company::store($data);
    }

    public function update($data, $id)
    {
        return Company::updateModel($data, $id);
    }
}
