<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SanctumExpiresAtMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_migration_preserves_existing_tokens_and_rolls_back(): void
    {
        DB::table('personal_access_tokens')->insert([
            'name' => 'legacy',
            'token' => hash('sha256', 'legacy-token'),
            'abilities' => json_encode(['*']),
        ]);

        $migration = require database_path('migrations/2026_09_07_000000_add_expires_at_to_personal_access_tokens_table.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'expires_at'));
        $this->assertSame('legacy', DB::table('personal_access_tokens')->value('name'));
        $this->assertNull(DB::table('personal_access_tokens')->value('expires_at'));

        $migration->down();

        $this->assertFalse(Schema::hasColumn('personal_access_tokens', 'expires_at'));
        $this->assertSame('legacy', DB::table('personal_access_tokens')->value('name'));
    }
}
