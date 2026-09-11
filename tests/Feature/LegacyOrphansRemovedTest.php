<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LegacyOrphansRemovedTest extends TestCase
{
    public function test_retired_group_management_and_installer_artifacts_are_absent(): void
    {
        $this->assertFileDoesNotExist(app_path('Http/Controllers/GrupoController.php'));
        $this->assertFileDoesNotExist(resource_path('views/cadastros/grupos/index.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/cadastros/grupos/create.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/cadastros/grupos/edit.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/cadastros/usuarios/install.blade.php'));
        $this->assertFalse(Route::has('grupos.index'));
        $this->assertFalse(Route::has('install'));
        $this->assertFalse(Route::has('install.store'));

        $controller = file_get_contents(app_path('Http/Controllers/UserController.php'));
        $this->assertStringNotContainsString('function install(', $controller);
        $this->assertStringNotContainsString('function installStore(', $controller);
    }

    public function test_unused_legacy_blade_components_and_layouts_are_absent(): void
    {
        $paths = [
            'views/components/action-menu.blade.php',
            'views/components/attachment-uploader.blade.php',
            'views/components/empty-state.blade.php',
            'views/components/filter-bar.blade.php',
            'views/components/form-actions.blade.php',
            'views/components/page-header.blade.php',
            'views/components/sla-indicator.blade.php',
            'views/components/status-badge.blade.php',
            'views/layouts/app.blade.php',
            'views/layouts/notification-center.blade.php',
        ];

        foreach ($paths as $path) {
            $this->assertFileDoesNotExist(resource_path(substr($path, strlen('views/'))), $path);
        }
    }
}
