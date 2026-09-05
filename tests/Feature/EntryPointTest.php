<?php

namespace Tests\Feature;

use Tests\TestCase;

class EntryPointTest extends TestCase
{
    public function test_root_redirects_to_home(): void
    {
        $this->get('/')->assertRedirect('/home');
    }

    public function test_guest_is_redirected_to_login_from_home(): void
    {
        $this->get('/home')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk();
    }
}
