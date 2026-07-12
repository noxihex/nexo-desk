<?php

namespace Tests\Unit;

use App\Support\AttachmentRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AttachmentRulesTest extends TestCase
{
    public function test_accepts_five_supported_files(): void
    {
        $files = array_fill(0, 5, UploadedFile::fake()->image('evidencia.jpg', 20, 20));

        $this->assertFalse(Validator::make(['anexos' => $files], AttachmentRules::for('anexos'))->fails());
    }

    public function test_rejects_six_files(): void
    {
        $files = array_fill(0, 6, UploadedFile::fake()->image('evidencia.jpg', 20, 20));

        $validator = Validator::make(['anexos' => $files], AttachmentRules::for('anexos'));
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('anexos', $validator->errors()->toArray());
    }

    public function test_rejects_oversized_file_and_unsupported_format(): void
    {
        $atLimit = UploadedFile::fake()->create('limite.pdf', 10240, 'application/pdf');
        $large = UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf');
        $unsupported = UploadedFile::fake()->create('programa.exe', 1, 'application/octet-stream');

        $this->assertFalse(Validator::make(['attachments' => [$atLimit]], AttachmentRules::for('attachments'))->fails());
        $this->assertTrue(Validator::make(['attachments' => [$large]], AttachmentRules::for('attachments'))->fails());
        $this->assertTrue(Validator::make(['attachments' => [$unsupported]], AttachmentRules::for('attachments'))->fails());
    }
}
