<?php

namespace Tests\Feature;

use Tests\TestCase;

class AttachmentUploaderViewTest extends TestCase
{
    public function test_all_ticket_attachment_flows_use_the_shared_component(): void
    {
        $views = [
            'tickets/create.blade.php' => 'anexos[]',
            'tickets/cliente/create.blade.php' => 'anexos[]',
            'tickets/show.blade.php' => 'attachments[]',
            'tickets/cliente/show.blade.php' => 'attachments[]',
        ];

        foreach ($views as $path => $name) {
            $view = file_get_contents(resource_path('views/' . $path));
            $this->assertStringContainsString('<x-attachment-uploader name="' . $name . '"', $view);
            $this->assertStringNotContainsString('addAttachmentField', $view);
        }

        $component = file_get_contents(resource_path('views/components/attachment-uploader.blade.php'));
        $this->assertStringContainsString("'maxSizeMb' => 10", $component);
    }
}
