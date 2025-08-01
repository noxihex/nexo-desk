<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    // Exibe a lista de backups
    public function index()
    {
        $backups = Backup::orderBy('data_hora', 'desc')->get();
        return view('administracao.backup.index', compact('backups'));
    }




    // Permite download de backups com status "sucesso"
    public function download($id)
{
    $backup = Backup::findOrFail($id);

    // Verifique se o status do backup é 'sucesso'
    if ($backup->status !== 'sucesso') {
        return redirect()->route('backup.index')->with('error', 'O backup falhou e não está disponível para download.');
    }

    // Ajusta o caminho do arquivo para ser relativo ao storage
    // Removemos o caminho absoluto até 'storage/app' deixando apenas a parte necessária
    $relativePath = preg_replace('#^.*storage/app/#', '', $backup->local_arquivo);

    // Verifica se o arquivo existe no caminho especificado
    if (Storage::disk('local')->exists($relativePath)) {
        // Realiza o download do arquivo
        return Storage::disk('local')->download($relativePath);
    }

    // Retorna erro se o arquivo não foi encontrado
    return redirect()->route('backup.index')->with('error', 'Arquivo de backup não encontrado.');
}

}
