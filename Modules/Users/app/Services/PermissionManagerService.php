<?php

namespace Modules\Users\Services;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class PermissionManagerService
{
    public function sync(): int
    {
        $routes = Route::getRoutes();
        $permissions = [];

        foreach ($routes as $route) {
            $action = $route->getActionName();
            $uri = $route->uri();
            $methods = $route->methods();
            $name = $route->getName();

            // ✅ نأخذ فقط راوتات الـ API الخاصة بالموديولات
            if (Str::startsWith($uri, 'api/')) {
                $module = $this->extractModuleName($action);

                if ($name && $module) {
                    $permissions[] = [
                        'name'   => $name,
                        'module' => $module,
                        'uri'    => $uri,
                        'method' => implode('|', $methods),
                    ];
                }
            }
        }

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm['name'],
            ], [
                'guard_name' => 'api', // 🔥 مهم
                'group'      => $perm['module'],
                'label'      => strtoupper($perm['method']) . ' ' . $perm['uri'],
            ]);
        }

        return count($permissions);
    }

    /**
     * استخراج اسم الموديول من الـ Action
     */
    private function extractModuleName(string $action): ?string
    {
        // مثال: Modules\Users\Http\Controllers\UserController@index
        if (Str::contains($action, 'Modules')) {
            $parts = explode('\\', $action);
            return $parts[1] ?? null; // Users, HR, Finance ...
        }

        return null;
    }
}
