<?php

namespace App\Livewire\Modern\ClientTickets;

use App\Actions\Tickets\AuthorizeClientTicketFlow;
use App\Actions\Tickets\ClientTicketListing;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ClientTicketIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'viewCompanyTickets', except: false)]
    public bool $viewCompanyTickets = false;

    public ?string $success = null;

    public function boot(): void
    {
        app(AuthorizeClientTicketFlow::class)->client();
    }

    public function mount(): void
    {
        $user = app(AuthorizeClientTicketFlow::class)->client();
        if ($user->empresa_id === null) {
            $this->viewCompanyTickets = false;
        }
        $this->success = session('success');
    }

    public function applySearch(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function toggleCompanyView(): void
    {
        $user = app(AuthorizeClientTicketFlow::class)->client();
        abort_if($user->empresa_id === null, 403);
        $this->viewCompanyTickets = ! $this->viewCompanyTickets;
        $this->resetPage();
    }

    private function returnUrl(): string
    {
        return route('tickets.cliente.index', array_filter([
            'search' => trim($this->search),
            'viewCompanyTickets' => $this->viewCompanyTickets ? '1' : null,
            'page' => $this->getPage() > 1 ? $this->getPage() : null,
        ], fn ($value) => $value !== '' && $value !== null));
    }

    public function render()
    {
        $user = app(AuthorizeClientTicketFlow::class)->client();

        return view('livewire.modern.client-tickets.client-ticket-index', [
            'tickets' => app(ClientTicketListing::class)->handle($user, $this->search, $this->viewCompanyTickets),
            'companyAvailable' => $user->empresa_id !== null,
            'returnUrl' => $this->returnUrl(),
        ]);
    }
}
