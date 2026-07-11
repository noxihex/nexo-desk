<?php

namespace Tests\Feature;

use Tests\TestCase;

class DarkModeViewTest extends TestCase
{
    public function test_theme_toggle_is_rendered_before_custom_navigation_items(): void
    {
        $navbar = file_get_contents(resource_path('views/vendor/adminlte/partials/navbar/navbar.blade.php'));

        $togglePosition = strpos($navbar, "@include('layouts.theme-toggle')");
        $customItemsPosition = strpos($navbar, "@yield('content_top_nav_right')");

        $this->assertNotFalse($togglePosition);
        $this->assertNotFalse($customItemsPosition);
        $this->assertLessThan($customItemsPosition, $togglePosition);
    }

    public function test_all_internal_pages_use_the_layout_with_the_global_toggle(): void
    {
        $pageLayout = file_get_contents(resource_path('views/vendor/adminlte/page.blade.php'));
        $toggle = file_get_contents(resource_path('views/layouts/theme-toggle.blade.php'));

        $this->assertStringContainsString("@include('layouts.theme-init')", $pageLayout);
        $this->assertStringContainsString('id="btxThemeToggle"', $toggle);

        $internalViews = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $view = file_get_contents($file->getPathname());
            if (strpos($view, "@extends('adminlte::page')") !== false) {
                $internalViews[] = $file->getPathname();
            }
        }

        $this->assertGreaterThan(30, count($internalViews));
    }

    public function test_theme_script_defaults_to_light_and_handles_storage_failures(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));
        $earlyInitializer = file_get_contents(resource_path('views/layouts/theme-init.blade.php'));

        $this->assertStringContainsString("const themeStorageKey = 'btx-theme'", $script);
        $this->assertStringContainsString("=== 'dark' ? 'dark' : 'light'", $script);
        $this->assertStringContainsString('window.localStorage.setItem(themeStorageKey, theme)', $script);
        $this->assertStringContainsString("catch (error) {\n            return 'light';", $script);
        $this->assertStringContainsString("theme = 'light';", $earlyInitializer);
        $this->assertStringContainsString("localStorage.getItem('btx-theme') === 'dark'", $earlyInitializer);
    }

    public function test_my_tickets_keeps_parent_navigation_items(): void
    {
        $view = file_get_contents(resource_path('views/tickets/my.blade.php'));

        $this->assertStringContainsString('@parent', $view);
    }
}
