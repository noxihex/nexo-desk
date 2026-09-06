<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categoria_setor', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('setor_id')->constrained('setores')->onDelete('cascade');
            $table->unique(['categoria_id', 'setor_id']);
        });

        DB::table('categorias')
            ->whereNotNull('setor_id')
            ->orderBy('id')
            ->chunkById(500, function ($categorias) {
                $associacoes = $categorias->map(function ($categoria) {
                    return [
                        'categoria_id' => $categoria->id,
                        'setor_id' => $categoria->setor_id,
                    ];
                })->all();

                DB::table('categoria_setor')->insertOrIgnore($associacoes);
            });
    }

    public function down()
    {
        Schema::dropIfExists('categoria_setor');
    }
};
