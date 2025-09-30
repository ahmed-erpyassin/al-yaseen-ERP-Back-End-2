<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasFileAttributes
{
    protected static function bootHasFileAttributes()
    {
        static::creating(function ($model) {
            foreach ($model->getFileAttributes() as $attribute => $directory) {
                if (request()->hasFile($attribute)) {
                    $path = request()->file($attribute)->store($directory, 'public');
                    $model->{$attribute} = $path;
                }
            }
        });

        static::updating(function ($model) {
            foreach ($model->getFileAttributes() as $attribute => $directory) {
                if (request()->hasFile($attribute)) {
                    if ($model->getOriginal($attribute) && Storage::disk('public')->exists($model->getOriginal($attribute))) {
                        Storage::disk('public')->delete($model->getOriginal($attribute));
                    }
                    $path = request()->file($attribute)->store($directory, 'public');
                    $model->{$attribute} = $path;
                }
            }
        });

        static::deleting(function ($model) {
            foreach ($model->getFileAttributes() as $attribute => $directory) {
                if ($model->{$attribute} && Storage::disk('public')->exists($model->{$attribute})) {
                    Storage::disk('public')->delete($model->{$attribute});
                }
            }
        });
    }

    public function getFileAttributes(): array
    {
        // إذا الموديل معرف الـ property نرجعها
        if (property_exists($this, 'fileAttributes') && is_array($this->fileAttributes)) {
            return $this->fileAttributes;
        }

        // لو مش معرف → نرجع array فاضية بدل null
        return [];
    }
}
