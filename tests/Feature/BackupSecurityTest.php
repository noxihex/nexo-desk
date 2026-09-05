<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackupSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('backups');
    }

    public function test_guest_cannot_list_or_download_backups(): void
    {
        $backup = $this->backup('backup_guest.sql');

        $this->get(route('backup.index'))->assertRedirect('/login');
        $this->get(route('backup.download', $backup))->assertRedirect('/login');
    }

    public function test_non_administrator_cannot_list_or_download_backups(): void
    {
        $analyst = $this->userWithRole('analista');
        $backup = $this->backup('backup_analyst.sql');

        $this->actingAs($analyst)->get(route('backup.index'))->assertForbidden();
        $this->actingAs($analyst)->get(route('backup.download', $backup))->assertForbidden();
    }

    public function test_administrator_downloads_backup_from_private_disk(): void
    {
        $administrator = $this->userWithRole('administrador');
        $backup = $this->backup('backup_private.sql');
        Storage::disk('backups')->put('backup_private.sql', 'private sql content');

        $response = $this->actingAs($administrator)->get(route('backup.download', $backup));

        $response->assertOk()
            ->assertHeader('cache-control', 'max-age=0, no-store, private')
            ->assertHeader('content-type', 'application/octet-stream');
        $this->assertSame('private sql content', $response->streamedContent());
    }

    public function test_legacy_database_path_only_resolves_inside_private_disk(): void
    {
        $administrator = $this->userWithRole('administrador');
        $backup = $this->backup('/var/www/html/nexodesk/storage/app/public/backup/backup_legacy.sql');
        Storage::disk('backups')->put('backup_legacy.sql', 'legacy content');

        $this->actingAs($administrator)
            ->get(route('backup.download', $backup))
            ->assertOk();
    }

    public function test_arbitrary_or_traversal_path_cannot_select_a_file(): void
    {
        $administrator = $this->userWithRole('administrador');
        Storage::disk('backups')->put('secret.sql', 'must not be downloaded');

        foreach (['../../secret.sql', '/etc/secret.sql', 'folder/secret.sql'] as $path) {
            $backup = $this->backup($path);

            $this->actingAs($administrator)
                ->get(route('backup.download', $backup))
                ->assertRedirect(route('backup.index'))
                ->assertSessionHas('error');
        }
    }

    public function test_failed_or_missing_backup_is_not_downloaded(): void
    {
        $administrator = $this->userWithRole('administrador');
        $failed = $this->backup('backup_failed.sql', 'falha');
        $missing = $this->backup('backup_missing.sql');
        Storage::disk('backups')->put('backup_failed.sql', 'partial content');

        $this->actingAs($administrator)
            ->get(route('backup.download', $failed))
            ->assertRedirect(route('backup.index'));

        $this->actingAs($administrator)
            ->get(route('backup.download', $missing))
            ->assertRedirect(route('backup.index'));
    }

    public function test_backup_page_does_not_expose_absolute_server_path(): void
    {
        $administrator = $this->userWithRole('administrador');
        $path = '/var/www/html/nexodesk/storage/app/public/backup/backup_hidden.sql';
        $this->backup($path);

        $this->actingAs($administrator)
            ->get(route('backup.index'))
            ->assertOk()
            ->assertSee('backup_hidden.sql')
            ->assertDontSee($path);
    }

    private function backup(string $path, string $status = 'sucesso'): Backup
    {
        return Backup::create([
            'data_hora' => now(),
            'local_arquivo' => $path,
            'status' => $status,
        ]);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['status' => true]);
        $user->assignRole($role);

        return $user;
    }
}
