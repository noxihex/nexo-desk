<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategoriaSetorMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('setores', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setor_id')->nullable();
        });
    }

    public function test_migration_backfills_only_existing_legacy_associations_and_rolls_back_safely()
    {
        DB::table('setores')->insert([['id' => 10], ['id' => 20]]);
        DB::table('categorias')->insert([
            ['id' => 1, 'setor_id' => 10],
            ['id' => 2, 'setor_id' => null],
            ['id' => 3, 'setor_id' => 20],
        ]);

        $migration = require database_path('migrations/2026_09_06_000001_create_categoria_setor_table.php');
        $migration->up();

        $this->assertSame([
            ['categoria_id' => 1, 'setor_id' => 10],
            ['categoria_id' => 3, 'setor_id' => 20],
        ], DB::table('categoria_setor')->orderBy('categoria_id')->get()
            ->map(function ($row) {
                return (array) $row;
            })->all());

        $migration->down();

        $this->assertFalse(Schema::hasTable('categoria_setor'));
        $this->assertSame(10, DB::table('categorias')->where('id', 1)->value('setor_id'));
        $this->assertNull(DB::table('categorias')->where('id', 2)->value('setor_id'));
    }
}
