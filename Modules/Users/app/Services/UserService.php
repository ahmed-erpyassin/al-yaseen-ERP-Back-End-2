<?php

namespace Modules\Users\Services;

use Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll($paginate = 10)
    {
        return User::paginate($paginate);
    }

    public function findById($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data, $creatorId)
    {
        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = $creatorId;
        return User::create($data);
    }

    public function update(User $user, array $data, $updaterId)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $data['updated_by'] = $updaterId;

        $user->update($data);
        return $user;
    }

    public function delete(User $user)
    {
        return $user->delete();
    }

    public function restore(User $user)
    {
        return $user->restore();
    }

    public function forceDelete(User $user)
    {
        return $user->forceDelete();
    }
}
