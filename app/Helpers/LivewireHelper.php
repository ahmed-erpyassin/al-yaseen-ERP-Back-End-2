<?php

namespace App\Helpers;

use App\Services\Services;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

trait LivewireHelper
{
    private function setService($service)
    {
        return Services::createInstance($service) ?? new Services();
    }

    public function alertMessage($message, $type = "success", $position = "center")
    {
        // Success
        if ($type == "success") {
            LivewireAlert::title(__("Operation Successful"))
                ->text($message)
                ->success()
                ->toast()
                ->position($position)
                ->timer(3000)
                ->show();
        }

        // Error
        if ($type == "error") {
            LivewireAlert::title(__("Operation Failed"))
                ->text($message)
                ->error()
                ->toast()
                ->position($position)
                ->timer(3000)
                ->show();
        }

        // Info
        if ($type == "info") {
            LivewireAlert::title(__("Information"))
                ->text($message)
                ->info()
                ->toast()
                ->position($position)
                ->timer(3000)
                ->show();
        }

        // Warning
        if ($type == "warning") {
            LivewireAlert::title(__("Warning"))
                ->text($message)
                ->warning()
                ->toast()
                ->position($position)
                ->timer(3000)
                ->show();
        }
    }

    public function alertConfirm($message, $action)
    {
        LivewireAlert::title($message)
            ->withConfirmButton(__("Yes, proceed"))
            ->withCancelButton(__("Cancel"))
            ->onConfirm($action)
            ->show();
    }

    public function showDivAlert($message, $type = "success", $title = "")
    {
        $this->dispatch('show-div-alert', [
            'message' => $message,
            'type' => $type,
            'title' => $title
        ]);
    }
}
