<?php

namespace Tests\Feature;

use Tests\TestCase;

class PasswordResetAvailabilityTest extends TestCase
{
    public function test_password_reset_is_temporarily_unavailable_by_default(): void
    {
        config(['auth.password_reset_enabled' => false]);

        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'usuario@example.com']);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error', 'A recuperação automática de senha está temporariamente desativada. Entre em contato com o administrador.');
        $response->assertSessionHasInput('email', 'usuario@example.com');
    }
}
