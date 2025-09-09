<?php

namespace Modules\Companies\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Company;

class CompanyService
{
    public function createCompany(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            return Company::create($data);
        });
    }

    public function getCompanies($user)
    {
        return Company::where('user_id', $user->id)->get();
    }

    public function getCompanyById($id)
    {
        return Company::findOrFail($id);
    }

    public function updateCompany($id, array $data)
    {
        $company = Company::findOrFail($id);
        $company->update($data);

        return $company;
    }

    public function deleteCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
    }
}
