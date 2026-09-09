<?php

namespace App\Livewire\Modern\Reports;

use App\Actions\Cadastros\AuthorizeCatalogs;
use App\Actions\Reports\AnalystReport;
use App\Actions\Reports\CompanyReport;
use Illuminate\Http\Request;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

abstract class ReportPage extends Component
{
    protected bool $company = false;
    protected ?array $result = null;

    #[Url(except: '')]
    public string $data_inicio = '';
    #[Url(except: '')]
    public string $data_fim = '';
    #[Url(except: '')]
    public string $empresa_id = '';
    #[Url(except: '')]
    public string $usuario_id = '';
    #[Url(except: '')]
    public string $setor_id = '';
    #[Url(except: true)]
    public bool $ignore_open_tickets = true;

    #[Locked]
    public array $applied = [];

    public function boot(): void
    {
        app(AuthorizeCatalogs::class)->handle();
    }

    public function mount(): void
    {
        $key = $this->company ? 'empresa_id' : 'usuario_id';
        if (request()->has(['data_inicio', 'data_fim', $key])) {
            $this->applyFilters();
        }
        $this->data_inicio = $this->data_inicio ?: ($this->company ? now()->subMonth() : now()->subDay())->format('Y-m-d\TH:i');
        $this->data_fim = $this->data_fim ?: now()->format('Y-m-d\TH:i');
    }

    public function applyFilters(): void
    {
        $this->resetValidation();
        $filters = [
            'data_inicio' => $this->data_inicio,
            'data_fim' => $this->data_fim,
        ];
        if ($this->company) {
            $filters += ['empresa_id' => $this->empresa_id, 'setor_id' => $this->setor_id ?: null, 'ignore_open_tickets' => $this->ignore_open_tickets ? '1' : '0'];
        } else {
            $filters['usuario_id'] = $this->usuario_id;
        }
        $this->result = $this->report($filters);
        $this->applied = $filters;
    }

    protected function report(array $filters): array
    {
        return app($this->company ? CompanyReport::class : AnalystReport::class)
            ->handle(Request::create('/', 'GET', $filters));
    }

    public function render()
    {
        return view('livewire.modern.reports.report-page', ($this->result ?? $this->report($this->applied)) + [
            'company' => $this->company,
            'generated' => $this->applied !== [],
        ]);
    }
}
