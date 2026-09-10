<?php

namespace App\Livewire\Modern\Overview;

use App\Actions\Overview\ClientOverview as ClientOverviewData;
use App\Actions\Tickets\AuthorizeClientTicketFlow;
use Livewire\Component;

class ClientOverview extends Component
{
    public function boot(): void
    {
        app(AuthorizeClientTicketFlow::class)->client();
    }

    public function render()
    {
        $user = app(AuthorizeClientTicketFlow::class)->client();

        return view('livewire.modern.overview.client-overview', app(ClientOverviewData::class)->handle($user));
    }
}
