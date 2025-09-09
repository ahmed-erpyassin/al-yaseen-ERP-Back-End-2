<?php

namespace Modules\Companies\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Branch;

class BranchService
{
    public function createBranch(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            return Branch::create($data);
        });
    }

    public function getBranches($user)
    {
        return Branch::where('user_id', $user->id)->get();
    }

    public function getBranchById($id)
    {
        return Branch::findOrFail($id);
    }

    public function updateBranch($id, array $data)
    {
        $branch = Branch::findOrFail($id);
        $branch->update($data);
        return $branch;
    }

    public function deleteBranch($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();
    }
}
