<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LaravelUiRemovedTest extends TestCase
{
    public function test_laravel_ui_package_and_auth_scaffolding_are_absent(): void
    {
        $this->assertStringNotContainsString('laravel/ui', File::get(base_path('composer.json')));
        $this->assertStringNotContainsString('laravel/ui', File::get(base_path('composer.lock')));
        $this->assertFalse(class_exists('Laravel\\Ui\\UiServiceProvider'));
        $this->assertFileDoesNotExist(app_path('Http/Controllers/Auth/RegisterController.php'));
        $this->assertFileDoesNotExist(app_path('Http/Controllers/Auth/VerificationController.php'));
        $this->assertFileDoesNotExist(resource_path('views/auth/register.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/auth/verify.blade.php'));
    }

    public function test_authentication_routes_are_explicit_and_inactive_flows_stay_disabled(): void
    {
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringNotContainsString('Auth::routes', $routes);
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('logout'));
        $this->assertTrue(Route::has('password.request'));
        $this->assertTrue(Route::has('password.email'));
        $this->assertTrue(Route::has('password.reset'));
        $this->assertTrue(Route::has('password.update'));
        $this->assertTrue(Route::has('password.confirm'));
        $this->assertFalse(Route::has('register'));
        $this->assertFalse(Route::has('verification.notice'));
        $this->assertFalse(Route::has('verification.verify'));
        $this->assertFalse(Route::has('verification.resend'));
    }

    public function test_authentication_code_does_not_use_laravel_ui_traits(): void
    {
        $files = array_merge(
            File::allFiles(app_path('Actions/Auth')),
            File::allFiles(app_path('Http/Controllers/Auth')),
        );

        foreach ($files as $file) {
            $this->assertStringNotContainsString(
                'Illuminate\\Foundation\\Auth',
                $file->getContents(),
                $file->getPathname(),
            );
        }
    }
}
