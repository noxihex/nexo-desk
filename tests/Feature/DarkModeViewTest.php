<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DarkModeViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_toggle_is_rendered_before_custom_navigation_items(): void
    {
        $navbar = file_get_contents(resource_path('views/vendor/adminlte/partials/navbar/navbar.blade.php'));

        $togglePosition = strpos($navbar, "@include('layouts.theme-toggle')");
        $customItemsPosition = strpos($navbar, "@yield('content_top_nav_right')");

        $this->assertNotFalse($togglePosition);
        $this->assertNotFalse($customItemsPosition);
        $this->assertLessThan($customItemsPosition, $togglePosition);
    }

    public function test_shared_layout_initializes_the_theme_and_defines_the_toggle(): void
    {
        $pageLayout = file_get_contents(resource_path('views/vendor/adminlte/page.blade.php'));
        $toggle = file_get_contents(resource_path('views/layouts/theme-toggle.blade.php'));

        $this->assertStringContainsString("@include('layouts.theme-init')", $pageLayout);
        $this->assertStringContainsString('id="btxThemeToggle"', $toggle);

    }

    public function test_theme_script_defaults_to_light_and_handles_storage_failures(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));
        $earlyInitializer = file_get_contents(resource_path('views/layouts/theme-init.blade.php'));

        $this->assertStringContainsString("const themeStorageKey = 'btx-theme'", $script);
        $this->assertStringContainsString("=== 'dark' ? 'dark' : 'light'", $script);
        $this->assertStringContainsString('window.localStorage.setItem(themeStorageKey, theme)', $script);
        $this->assertMatchesRegularExpression("/catch\\s*\\(error\\)\\s*\\{\\s*return 'light';/", $script);
        $this->assertStringContainsString("theme = 'light';", $earlyInitializer);
        $this->assertStringContainsString("localStorage.getItem('btx-theme') === 'dark'", $earlyInitializer);
    }

    public function test_dark_mode_pagination_uses_readable_text_color(): void
    {
        $stylesheet = file_get_contents(resource_path('sass/app.scss'));

        $this->assertStringContainsString(
            '.page-item:not(.active):not(.disabled) .page-link { color: var(--btx-text); background: var(--btx-surface); border-color: var(--btx-border); }',
            $stylesheet
        );
        $this->assertStringContainsString(
            '.page-item.disabled .page-link { color: var(--btx-text-muted);',
            $stylesheet
        );
    }

    public function test_my_tickets_renders_the_global_theme_toggle_once(): void
    {
        Role::findOrCreate('analista', 'web');
        $user = User::factory()->create(['status' => true]);
        $user->assignRole('analista');

        $response = $this->actingAs($user)->get(route('tickets.my'))->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'id="btxThemeToggle"'));
    }
}
