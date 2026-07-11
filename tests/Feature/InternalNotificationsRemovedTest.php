<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InternalNotificationsRemovedTest extends TestCase
{
    public function test_internal_notification_routes_are_not_registered(): void
    {
        $this->assertFalse(Route::has('notificacoes.index'));
        $this->assertFalse(Route::has('notificacoes.marcarComoLida'));
    }
}
