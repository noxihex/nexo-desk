<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminLteRemovedTest extends TestCase
{
    public function test_adminlte_package_configuration_views_and_assets_are_absent(): void
    {
        $this->assertStringNotContainsString('jeroennoten/laravel-adminlte', File::get(base_path('composer.json')));
        $this->assertStringNotContainsString('jeroennoten/laravel-adminlte', File::get(base_path('composer.lock')));
        $this->assertStringNotContainsString('almasaeed2010/adminlte', File::get(base_path('composer.lock')));
        $this->assertFileDoesNotExist(config_path('adminlte.php'));
        $this->assertDirectoryDoesNotExist(resource_path('views/vendor/adminlte'));
        $this->assertDirectoryDoesNotExist(resource_path('lang/vendor/adminlte'));
        $this->assertDirectoryDoesNotExist(public_path('vendor'));
    }

    public function test_application_views_and_provider_do_not_reference_adminlte_or_bootstrap_pagination(): void
    {
        foreach (File::allFiles(resource_path('views')) as $view) {
            $this->assertStringNotContainsString('adminlte::', strtolower($view->getContents()), $view->getPathname());
        }

        $provider = File::get(app_path('Providers/AppServiceProvider.php'));
        $this->assertStringNotContainsString('Paginator::useBootstrap', $provider);
    }
}
