<?php

namespace Modules\Companies\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Industry;

class IndustryService
{
    public function createIndustry(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return Industry::create($data);
        });
    }

    public function getIndustries($user)
    {
        return Industry::where('user_id', $user->id)->get();
    }

    public function getIndustryById($id)
    {
        return Industry::findOrFail($id);
    }

    public function updateIndustry($id, array $data)
    {
        $industry = Industry::findOrFail($id);
        $industry->update($data);
        return $industry;
    }

    public function deleteIndustry($id)
    {
        $industry = Industry::findOrFail($id);
        $industry->delete();
    }
}
