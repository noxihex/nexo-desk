<?php

namespace Tests\Feature;

use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    public function test_sidebar_collapse_state_is_remembered_between_pages(): void
    {
        $this->assertTrue(config('adminlte.sidebar_collapse_remember'));
    }

    public function test_compact_sidebar_hides_open_submenus(): void
    {
        $this->assertContains(
            'nav-collapse-hide-child',
            preg_split('/\s+/', trim(config('adminlte.classes_sidebar_nav')))
        );
    }
}
