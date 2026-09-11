<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LegacyAssetsRemovedTest extends TestCase
{
    public function test_no_route_or_view_uses_the_retired_interface_stack(): void
    {
        foreach (Route::getRoutes() as $route) {
            $description = strtolower($route->uri().' '.$route->getActionName());

            $this->assertStringNotContainsString('adminlte', $description);
            $this->assertStringNotContainsString('laravel\\ui', $description);
        }

        foreach (File::allFiles(resource_path('views')) as $view) {
            $contents = strtolower($view->getContents());

            $this->assertStringNotContainsString('adminlte::', $contents, $view->getPathname());
            $this->assertStringNotContainsString('mix(', $contents, $view->getPathname());
            $this->assertStringNotContainsString('vendor/jquery', $contents, $view->getPathname());
            $this->assertStringNotContainsString('vendor/bootstrap', $contents, $view->getPathname());
        }
    }

    public function test_every_literal_controller_view_exists(): void
    {
        foreach (File::allFiles(app_path('Http/Controllers')) as $controller) {
            preg_match_all('/\\bview\\(\\s*[\'\"]([^\'\"]+)[\'\"]/', $controller->getContents(), $matches);

            foreach ($matches[1] as $view) {
                $this->assertTrue(View::exists($view), "View ausente referenciada por {$controller->getPathname()}: {$view}");
            }
        }
    }

    public function test_retired_dependencies_sources_and_public_assets_are_absent(): void
    {
        $composer = File::get(base_path('composer.json'));
        $package = File::get(base_path('package.json'));

        $this->assertStringNotContainsString('jeroennoten/laravel-adminlte', $composer);
        $this->assertStringNotContainsString('laravel/ui', $composer);
        $this->assertStringNotContainsString('laravel-mix', $package);
        $this->assertStringNotContainsString('bootstrap', $package);
        $this->assertFileDoesNotExist(base_path('webpack.mix.js'));
        $this->assertDirectoryDoesNotExist(resource_path('sass'));
        $this->assertDirectoryDoesNotExist(public_path('vendor'));
        $this->assertDirectoryDoesNotExist(public_path('js'));
        $this->assertDirectoryDoesNotExist(public_path('css'));
        $this->assertFileDoesNotExist(public_path('mix-manifest.json'));
    }
}
