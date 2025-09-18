<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public $model = Role::class;

    public function __construct()
    {
        $this->model = new Role();
    }

    public function model($id)
    {
        return Role::find($id);
    }

    public function data($filters, $sort_field, $sort_direction, $paginate = 10)
    {
        return Role::query()
            ->withCount(['permissions', 'users'])
            ->when($filters['search'], function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($filters['guard'], function ($query, $guard) {
                $query->where('guard_name', $guard);
            })
            ->orderBy($sort_field, $sort_direction)
            ->paginate($paginate);
    }

    public function delete($id)
    {
        return Role::deleteModel($id);
    }

    public function store($data)
    {
        return Role::firstOrCreate([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);
    }

    public function update($data, $id)
    {
        return  $this->model($id)->firstOrCreate([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);
    }

    public function allPermissions()
    {
        return Permission::all();
    }
}
