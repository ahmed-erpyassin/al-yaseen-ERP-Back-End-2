<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Users\Models\User;

class UserService
{
    public $model = User::class;

    public function __construct()
    {
        $this->model = new User();
    }

    public function model($id)
    {
        return User::find($id);
    }

    public function data($filters, $sort_field, $sort_direction, $paginate = 10)
    {
        $user = Auth::user();

        return User::data()
            ->filters($filters)
            ->reorder($sort_field, $sort_direction)
            ->paginate($paginate);
    }

    public function changeAccountStatus($id)
    {
        return User::changeAccountStatus($id);
    }

    public function delete($id)
    {
        return User::deleteModel($id);
    }

    public function store($data)
    {
        return User::store($data);
    }

    public function update($data, $id)
    {
        return User::updateModel($data, $id);
    }
}
