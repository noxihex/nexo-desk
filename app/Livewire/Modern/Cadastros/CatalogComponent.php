<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\AuthorizeCatalogs;
use Livewire\Component;

abstract class CatalogComponent extends Component
{
    public function boot(): void
    {
        app(AuthorizeCatalogs::class)->handle();
    }
}
