<?php

namespace Tests\Feature;

use App\Models\User;
use Flux\FluxManager;
use Illuminate\Foundation\Vite;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Livewire\LivewireManager;
use Tests\TestCase;

class ModernStackViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::share('errors', new ViewErrorBag);
    }

    public function test_modern_stack_is_registered_with_isolated_configuration(): void
    {
        $this->assertInstanceOf(LivewireManager::class, app('livewire'));
        $this->assertInstanceOf(FluxManager::class, app('flux'));
        $this->assertSame('components.layouts.modern', config('livewire.component_layout'));
        $this->assertSame('class', config('livewire.make_command.type'));
        $this->assertFalse(config('livewire.make_command.emoji'));
        $this->assertFalse(config('livewire.inject_assets'));
        $this->assertSame(storage_path('vite.hot'), app(Vite::class)->hotFile());
    }

    public function test_modern_layout_renders_accessible_sidebar_without_legacy_assets(): void
    {
        $this->withoutVite();

        $html = Blade::render(<<<'BLADE'
            <x-layouts.modern title="Teste" heading="Fundação moderna">
                <x-slot:actions><span>Ação</span></x-slot:actions>
                <x-slot:navigation><a href="/moderno">Página moderna</a></x-slot:navigation>
                <p>Conteúdo moderno</p>
            </x-layouts.modern>
        BLADE);

        $this->assertStringContainsString('<title>Teste · ', $html);
        $this->assertStringContainsString('id="modern-sidebar"', $html);
        $this->assertStringContainsString('aria-controls="modern-sidebar"', $html);
        $this->assertStringContainsString('aria-label="Abrir menu"', $html);
        $this->assertStringContainsString('aria-label="Fechar menu"', $html);
        $this->assertStringContainsString('x-on:keydown.escape.window', $html);
        $this->assertStringContainsString('x-bind:inert', $html);
        $this->assertStringContainsString('x-trap.inert.noscroll', $html);
        $this->assertStringContainsString('Página moderna', $html);
        $this->assertStringContainsString('Conteúdo moderno', $html);
        $this->assertStringNotContainsString('adminlte', strtolower($html));
        $this->assertStringNotContainsString('bootstrap', strtolower($html));
        $this->assertStringNotContainsString('jquery', strtolower($html));
        $this->assertStringNotContainsString('mix-manifest', strtolower($html));
    }

    public function test_authenticated_layout_renders_user_appearance_and_logout_controls(): void
    {
        $this->withoutVite();
        $this->actingAs(new User([
            'name' => 'Maria da Silva',
            'email' => 'maria@example.com',
        ]));

        $html = Blade::render('<x-layouts.modern><p>Conteúdo</p></x-layouts.modern>');

        $this->assertStringContainsString('Maria da Silva', $html);
        $this->assertStringContainsString('Tema claro', $html);
        $this->assertStringContainsString('Tema escuro', $html);
        $this->assertStringContainsString('Usar tema do sistema', $html);
        $this->assertStringContainsString('action="'.route('logout').'"', $html);
        $this->assertStringContainsString('Sair', $html);
    }

    public function test_modern_layout_renders_flash_messages_and_general_errors(): void
    {
        $this->withoutVite();
        session()->flash('success', 'Operação concluída.');
        session()->flash('error', 'Não foi possível concluir.');

        View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([
            'form' => ['Revise os dados informados.'],
        ])));

        $html = Blade::render('<x-layouts.modern><p>Conteúdo</p></x-layouts.modern>');

        $this->assertStringContainsString('Operação concluída.', $html);
        $this->assertStringContainsString('Não foi possível concluir.', $html);
        $this->assertStringContainsString('Revise os campos informados', $html);
        $this->assertStringContainsString('Revise os dados informados.', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
    }

    public function test_form_wrappers_forward_reactive_and_accessibility_attributes(): void
    {
        $errors = (new ViewErrorBag)->put('default', new MessageBag([
            'email' => ['Informe um e-mail válido.'],
        ]));

        View::share('errors', $errors);

        $html = Blade::render(<<<'BLADE'
            <div>
                <x-modern.button type="submit" icon="check" wire:click="save" x-on:mouseenter="hovered = true" aria-label="Salvar registro" class="extra-button">
                    Salvar
                </x-modern.button>

                <x-modern.button href="/moderno" disabled :loading="false">Indisponível</x-modern.button>

                <x-modern.input name="email" label="E-mail" description="E-mail principal" wire:model="email" data-test="email" />

                <x-modern.select name="priority" label="Prioridade" placeholder="Selecione" :options="['normal' => 'Normal']" selected="normal" wire:model="priority">
                    <x-modern.select.option value="high" disabled>Alta</x-modern.select.option>
                </x-modern.select>
            </div>
        BLADE);

        $this->assertStringContainsString('wire:click="save"', $html);
        $this->assertStringContainsString('x-on:mouseenter="hovered = true"', $html);
        $this->assertStringContainsString('aria-label="Salvar registro"', $html);
        $this->assertStringContainsString('extra-button', $html);
        $this->assertStringContainsString('href="/moderno"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('tabindex="-1"', $html);
        $this->assertStringContainsString('wire:model="email"', $html);
        $this->assertStringContainsString('data-test="email"', $html);
        $this->assertStringContainsString('Informe um e-mail válido.', $html);
        $this->assertStringContainsString('wire:model="priority"', $html);
        $this->assertStringContainsString('Normal', $html);
        $this->assertStringContainsString('Alta', $html);
    }

    public function test_content_table_modal_and_pagination_wrappers_render(): void
    {
        $paginator = new LengthAwarePaginator(
            items: collect([['id' => 11, 'subject' => 'Chamado moderno']]),
            total: 25,
            perPage: 10,
            currentPage: 2,
            options: ['path' => '/moderno'],
        );

        $html = Blade::render(<<<'BLADE'
            <div>
                <x-modern.badge color="green" data-test="badge">Ativo</x-modern.badge>

                <x-modern.card title="Resumo" description="Descrição" data-test="card">
                    <x-slot:actions>Ações do card</x-slot:actions>
                    Conteúdo do card
                    <x-slot:footer>Rodapé do card</x-slot:footer>
                </x-modern.card>

                <x-modern.table :paginator="$paginator" data-test="table">
                    <x-modern.table.columns>
                        <x-modern.table.column>Chamado</x-modern.table.column>
                    </x-modern.table.columns>
                    <x-modern.table.rows>
                        <x-modern.table.row>
                            <x-modern.table.cell>Chamado moderno</x-modern.table.cell>
                        </x-modern.table.row>
                    </x-modern.table.rows>
                </x-modern.table>

                <x-modern.modal name="confirm" title="Confirmar" description="Revise a ação" data-test="modal">
                    <x-slot:trigger><button type="button">Abrir modal</button></x-slot:trigger>
                    Conteúdo do modal
                    <x-slot:actions><button type="button">Continuar</button></x-slot:actions>
                </x-modern.modal>

                <x-modern.pagination :paginator="$paginator" scroll-to="#content" data-test="pagination" />
            </div>
        BLADE, compact('paginator'));

        $this->assertStringContainsString('data-flux-badge', $html);
        $this->assertStringContainsString('data-flux-card', $html);
        $this->assertStringContainsString('data-flux-table', $html);
        $this->assertStringContainsString('data-modal="confirm"', $html);
        $this->assertStringContainsString('data-flux-pagination', $html);
        $this->assertStringContainsString('data-test="badge"', $html);
        $this->assertStringContainsString('data-test="card"', $html);
        $this->assertStringContainsString('data-test="table"', $html);
        $this->assertStringContainsString('data-test="modal"', $html);
        $this->assertStringContainsString('data-test="pagination"', $html);
        $this->assertStringContainsString('x-on:click', $html);
        $this->assertStringContainsString('Chamado moderno', $html);
        $this->assertStringContainsString('Conteúdo do modal', $html);
    }

    public function test_modern_action_buttons_follow_the_legacy_semantic_palette(): void
    {
        $ticketListing = File::get(resource_path('views/livewire/modern/tickets/ticket-index.blade.php'));
        $report = File::get(resource_path('views/livewire/modern/reports/report-page.blade.php'));

        $this->assertStringContainsString('variant="filled" color="sky"', $ticketListing);
        $this->assertStringContainsString('variant="filled" color="amber"', $ticketListing);
        $this->assertStringContainsString('variant="filled" color="red"', $ticketListing);
        $this->assertStringContainsString('variant="filled" color="green"', $ticketListing);
        $this->assertStringContainsString('variant="filled" color="sky"', $report);

        foreach (File::allFiles(resource_path('views/livewire/modern')) as $view) {
            $contents = $view->getContents();

            $this->assertSame(
                0,
                preg_match('/<x-modern\.button\b[^>]*\bvariant="danger"/s', $contents),
                $view->getPathname().' must not use the strong danger button variant.',
            );

            if (str_ends_with($view->getFilename(), '-form.blade.php') || $view->getFilename() === 'settings.blade.php') {
                $this->assertStringContainsString('variant="filled" color="green"', $contents, $view->getPathname());
            }

            if (str_ends_with($view->getFilename(), '-index.blade.php')) {
                $this->assertStringContainsString('color="amber"', $contents, $view->getPathname());
                $this->assertTrue(
                    str_contains($contents, 'color="red"') || str_contains($contents, "'red'"),
                    $view->getPathname().' must use the soft red palette for destructive actions.',
                );
            }
        }
    }

    public function test_modern_sources_and_legacy_templates_remain_isolated(): void
    {
        $css = File::get(resource_path('css/modern.css'));
        $javascript = File::get(resource_path('js/modern.js'));
        $layout = File::get(resource_path('views/components/layouts/modern.blade.php'));
        $viteConfig = File::get(base_path('vite.config.mjs'));

        $this->assertStringContainsString("@import 'tailwindcss' source(none);", $css);
        $this->assertStringContainsString("@import '../../vendor/livewire/flux/dist/flux.css';", $css);
        $this->assertStringContainsString("import '../css/modern.css';", $javascript);
        $this->assertStringContainsString("hotFile: 'storage/vite.hot'", $viteConfig);
        $this->assertStringContainsString('strictPort: true', $viteConfig);
        $this->assertStringContainsString("@vite('resources/js/modern.js')", $layout);
        $this->assertStringContainsString('@livewireStyles', $layout);
        $this->assertStringContainsString('@fluxAppearance', $layout);
        $this->assertStringNotContainsString('flux:sidebar', $layout);

        foreach (File::allFiles(resource_path('views')) as $view) {
            $contents = $view->getContents();

            if (! str_contains($contents, "@extends('adminlte::")
                && ! str_contains($contents, '@extends("adminlte::')) {
                continue;
            }

            $this->assertStringNotContainsString('@vite', $contents, $view->getPathname());
            $this->assertStringNotContainsString('@livewireStyles', $contents, $view->getPathname());
            $this->assertStringNotContainsString('@livewireScripts', $contents, $view->getPathname());
            $this->assertStringNotContainsString('@fluxAppearance', $contents, $view->getPathname());
            $this->assertStringNotContainsString('@fluxScripts', $contents, $view->getPathname());
        }
    }
}
