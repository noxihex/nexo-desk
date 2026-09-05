<?php

namespace App\Http\Controllers;

use App\Models\Backup;
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

        if ($backup->status !== 'sucesso') {
            return redirect()->route('backup.index')->with('error', 'O backup falhou e não está disponível para download.');
        }

        $filename = $this->resolveFilename($backup->local_arquivo);

        if ($filename !== null && Storage::disk('backups')->exists($filename)) {
            return Storage::disk('backups')->download($filename, $filename, [
                'Cache-Control' => 'private, no-store, max-age=0',
                'Content-Type' => 'application/octet-stream',
                'Pragma' => 'no-cache',
            ]);
        }

        return redirect()->route('backup.index')->with('error', 'Arquivo de backup não encontrado.');
    }

    /**
     * Aceita o formato novo (somente o nome) e o caminho absoluto legado,
     * mas nunca permite que o registro escolha um diretório no servidor.
     */
    private function resolveFilename(?string $storedPath): ?string
    {
        if ($storedPath === null || $storedPath === '' || strpos($storedPath, "\0") !== false) {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $storedPath);

        if (strpos($normalizedPath, '/') === false) {
            return in_array($normalizedPath, ['.', '..'], true) ? null : $normalizedPath;
        }

        if (preg_match('#(?:^|/)storage/app/public/backup/([^/]+)$#', $normalizedPath, $matches) !== 1) {
            return null;
        }

        return in_array($matches[1], ['.', '..'], true) ? null : $matches[1];
    }

}
