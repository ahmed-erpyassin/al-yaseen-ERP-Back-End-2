<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Modules\FinancialAccounts\Models\FiscalYear;

class AppContext
{
    public static function userId(): ?int
    {
        return Auth::id();
    }

    public static function companyId(): ?int
    {
        return Auth::user()?->company_id;
    }

    public static function fiscalYearId(): ?int
    {
        return FiscalYear::currentYearId(self::companyId(), self::userId());
    }

    public static function branchId(): ?int
    {
        return Auth::user()?->branch_id;
    }

    public static function all(): array
    {
        return [
            'user_id'        => self::userId(),
            'company_id'     => self::companyId(),
            'branch_id'      => self::branchId(),
            'fiscal_year_id' => self::fiscalYearId(),
        ];
    }
}
