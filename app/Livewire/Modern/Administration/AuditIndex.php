<?php

namespace App\Livewire\Modern\Administration;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;

class AuditIndex extends Component
{
    use WithPagination;

    private const EVENTS = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];

    #[Url(as: 'user_id', except: '')]
    public string $userId = '';

    #[Url(except: '')]
    public string $event = '';

    #[Url(as: 'auditable_type', except: '')]
    public string $auditableType = '';

    #[Url(as: 'auditable_id', except: '')]
    public string $auditableId = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function boot(): void
    {
        abort_unless(auth()->user()?->can('acesso admin'), 403);
    }

    public function applyFilters(): void
    {
        $this->validate();
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('userId', 'event', 'auditableType', 'auditableId', 'from', 'to');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.modern.administration.audit-index', [
            'audits' => $this->audits()->latest()->paginate(10),
            'auditUsers' => $this->auditUsers(),
            'auditableTypes' => $this->auditableTypes(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'userId' => ['nullable', 'integer', 'exists:users,id'],
            'event' => ['nullable', Rule::in(self::EVENTS)],
            'auditableType' => ['nullable', Rule::in($this->auditableTypes()->all())],
            'auditableId' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    private function audits(): Builder
    {
        $audits = Audit::query()->with('user');

        if ($this->userId !== '') {
            $audits->where('user_type', User::class)->where('user_id', $this->userId);
        }

        if ($this->event !== '') {
            $audits->where('event', $this->event);
        }

        if ($this->auditableType !== '') {
            $audits->where('auditable_type', $this->auditableType);
        }

        if ($this->auditableId !== '') {
            $audits->where('auditable_id', $this->auditableId);
        }

        if ($this->from !== '') {
            $audits->where('created_at', '>=', Carbon::parse($this->from)->startOfDay());
        }

        if ($this->to !== '') {
            $audits->where('created_at', '<=', Carbon::parse($this->to)->endOfDay());
        }

        return $audits;
    }

    private function auditUsers(): Collection
    {
        return User::query()
            ->with('roles')
            ->whereIn('id', Audit::query()
                ->where('user_type', User::class)
                ->whereNotNull('user_id')
                ->select('user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function auditableTypes(): Collection
    {
        return Audit::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');
    }
}
