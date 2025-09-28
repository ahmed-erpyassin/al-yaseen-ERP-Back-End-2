<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasUserStamps
{
    protected static function bootHasUserStamps()
    {
        // عند الإنشاء → ضيف created_by
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id(); // أول مرة بيكون نفسه
            }
        });

        // عند التحديث → حدّث updated_by
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
