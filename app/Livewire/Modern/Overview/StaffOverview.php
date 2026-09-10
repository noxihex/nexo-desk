<?php

namespace App\Livewire\Modern\Overview;

use App\Actions\Overview\StaffOverview as StaffOverviewData;
use App\Actions\Tickets\AuthorizeTicketLists;
use Livewire\Attributes\Url;
use Livewire\Component;

class StaffOverview extends Component
{
    #[Url(except: 'desc')]
    public string $order = 'desc';

    #[Url(except: '')]
    public string $analista = '';

    #[Url(except: '')]
    public string $setor = '';

    #[Url(except: '')]
    public string $empresa = '';

    public function boot(): void
    {
        app(AuthorizeTicketLists::class)->handle();
    }

    public function updatedOrder(string $value): void
    {
        $this->order = in_array($value, ['asc', 'desc'], true) ? $value : 'desc';
    }

    public function clearFilters(): void
    {
        app(AuthorizeTicketLists::class)->handle();
        $this->reset('analista', 'setor', 'empresa');
        $this->order = 'desc';
    }

    public function render()
    {
        $user = app(AuthorizeTicketLists::class)->handle();

        return view('livewire.modern.overview.staff-overview', app(StaffOverviewData::class)->handle($user, [
            'order' => $this->order,
            'analista' => $this->analista,
            'setor' => $this->setor,
            'empresa' => $this->empresa,
        ]) + [
            'administrator' => $user->hasRole('administrador'),
            'manager' => $user->hasAnyRole(['supervisor', 'administrador']),
            'userSectorId' => $user->setor_id,
            'returnUrl' => $this->returnUrl(),
        ]);
    }

    private function returnUrl(): string
    {
        return route('home', array_filter([
            'order' => $this->order !== 'desc' ? $this->order : null,
            'analista' => $this->analista,
            'setor' => $this->setor,
            'empresa' => $this->empresa,
        ], fn ($value) => $value !== '' && $value !== null));
    }
}
