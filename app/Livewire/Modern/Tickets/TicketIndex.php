<?php

namespace App\Livewire\Modern\Tickets;

use App\Actions\Tickets\AuthorizeTicketLists;
use App\Actions\Tickets\DeleteTicket;
use App\Actions\Tickets\TicketListing;
use App\Models\Ticket;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketIndex extends Component
{
    use WithPagination;

    #[Locked]
    public string $section;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'created_at')]
    public string $sort = 'created_at';

    #[Url(as: 'showClosed', except: false)]
    public bool $showClosed = false;

    #[Url(except: '')]
    public string $setor_id = '';

    #[Url(except: '')]
    public string $categoria_id = '';

    #[Url(except: '')]
    public string $empresa_id = '';

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteSubject = '';

    public bool $showDelete = false;
    public ?string $success = null;

    public function boot(): void
    {
        app(AuthorizeTicketLists::class)->handle();
    }

    public function mount(string $section): void
    {
        abort_unless(in_array($section, [TicketListing::GENERAL, TicketListing::MINE, TicketListing::PENDING], true), 404);
        $this->section = $section;
        $this->success = session('success');

        if ($section === TicketListing::GENERAL && ! request()->has('showClosed') && trim($this->search) !== '') {
            $this->showClosed = true;
        }
    }

    public function applyFilters(): void
    {
        $this->sort = in_array($this->sort, ['created_at', 'updated_at', 'sla'], true) ? $this->sort : 'created_at';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'setor_id', 'categoria_id', 'empresa_id', 'showClosed');
        $this->sort = 'created_at';
        $this->resetPage();
    }

    public function updatedShowClosed(): void
    {
        if ($this->section === TicketListing::MINE) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $user = app(AuthorizeTicketLists::class)->handle();
        abort_unless($user->hasRole('administrador'), 403);
        $ticket = Ticket::findOrFail($id);
        $this->deleteId = $ticket->id;
        $this->deleteSubject = $ticket->assunto;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        abort_if($this->deleteId === null, 404);
        app(DeleteTicket::class)->handle($this->deleteId);
        $this->reset('deleteId', 'deleteSubject', 'showDelete');
        $this->success = 'Ticket excluído com sucesso!';

        $lastPage = $this->listing()['tickets']->lastPage();
        $this->setPage(min($this->getPage(), $lastPage));
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'sort' => $this->sort,
            'showClosed' => $this->showClosed,
            'setor_id' => $this->setor_id,
            'categoria_id' => $this->categoria_id,
            'empresa_id' => $this->empresa_id,
        ];
    }

    private function listing(): array
    {
        $user = app(AuthorizeTicketLists::class)->handle();

        return app(TicketListing::class)->handle($user, $this->section, $this->filters());
    }

    private function returnUrl(): string
    {
        $route = match ($this->section) {
            TicketListing::MINE => 'tickets.my',
            TicketListing::PENDING => 'tickets.pendentes',
            default => 'tickets.index',
        };
        $query = ['page' => $this->getPage() > 1 ? $this->getPage() : null];

        if ($this->section === TicketListing::GENERAL) {
            $query += [
                'search' => trim($this->search),
                'sort' => $this->sort !== 'created_at' ? $this->sort : null,
                'showClosed' => $this->showClosed ? '1' : null,
                'setor_id' => $this->setor_id,
                'categoria_id' => $this->categoria_id,
                'empresa_id' => $this->empresa_id,
            ];
        } elseif ($this->section === TicketListing::MINE) {
            $query += [
                'sort' => $this->sort !== 'created_at' ? $this->sort : null,
                'showClosed' => $this->showClosed ? '1' : null,
            ];
        }

        return route($route, array_filter($query, fn ($value) => $value !== '' && $value !== null));
    }

    public function render()
    {
        $user = app(AuthorizeTicketLists::class)->handle();

        return view('livewire.modern.tickets.ticket-index', $this->listing() + [
            'administrator' => $user->hasRole('administrador'),
            'manager' => $user->hasAnyRole(['supervisor', 'administrador']),
            'returnUrl' => $this->returnUrl(),
        ]);
    }
}
