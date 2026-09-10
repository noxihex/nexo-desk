<?php

namespace Tests\Feature;

use Tests\TestCase;

class AttachmentUploaderViewTest extends TestCase
{
    public function test_all_modern_ticket_attachment_flows_use_the_shared_component(): void
    {
        $modernCreate = file_get_contents(resource_path('views/livewire/modern/tickets/ticket-form.blade.php'));
        $modernShow = file_get_contents(resource_path('views/livewire/modern/tickets/ticket-show.blade.php'));
        $clientCreate = file_get_contents(resource_path('views/livewire/modern/client-tickets/client-ticket-create.blade.php'));
        $clientShow = file_get_contents(resource_path('views/livewire/modern/client-tickets/client-ticket-show.blade.php'));
        $modernUploader = file_get_contents(resource_path('views/components/modern/file-upload.blade.php'));

        $this->assertStringContainsString('<x-modern.file-upload model="newAnexos"', $modernCreate);
        $this->assertStringContainsString('<x-modern.file-upload model="newMessageAttachments"', $modernShow);
        $this->assertStringContainsString('<x-modern.file-upload model="newAnexos"', $clientCreate);
        $this->assertStringContainsString('<x-modern.file-upload model="newMessageAttachments"', $clientShow);
        $this->assertStringContainsString('wire:model="{{ $model }}"', $modernUploader);
        $this->assertStringContainsString('multiple', $modernUploader);
        $this->assertStringNotContainsString('x-attachment-uploader', $modernCreate.$modernShow.$clientCreate.$clientShow);
    }
}
