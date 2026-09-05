<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ManageApiKey extends Command
{
    protected $signature = 'app:api-key
                            {action? : Operação: create|criar ou revoke|revogar}
                            {identifier? : E-mail do usuário ao criar ou ID da chave ao revogar}
                            {--name= : Nome identificador da chave ao criar}
                            {--all : Revoga todas as chaves do usuário informado}
                            {--force : Confirma a revogação sem perguntar}';

    protected $description = 'Cria ou revoga chaves de API do Laravel Sanctum';

    public function handle()
    {
        $action = strtolower(trim((string) $this->argument('action')));

        if ($action === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Informe uma operação ou execute o comando em um terminal interativo para abrir o menu.');

                return 1;
            }

            return $this->interactiveMenu();
        }

        if (in_array($action, ['create', 'criar'], true)) {
            if ((string) $this->argument('identifier') === '' && $this->input->isInteractive()) {
                return $this->interactiveCreateKey();
            }

            return $this->createKey();
        }

        if (in_array($action, ['revoke', 'revogar'], true)) {
            if ((string) $this->argument('identifier') === '' && $this->input->isInteractive()) {
                return $this->option('all')
                    ? $this->interactiveRevokeAllKeys()
                    : $this->interactiveRevokeKey();
            }

            return $this->revokeKey();
        }

        $this->error('Operação inválida. Use create (criar) ou revoke (revogar).');

        return 1;
    }

    private function interactiveMenu(): int
    {
        $choice = $this->choiceIndex('O que deseja fazer?', [
            '1' => 'Criar uma chave',
            '2' => 'Revogar uma chave',
            '3' => 'Revogar todas as chaves de um usuário',
            '4' => 'Sair',
        ], '1');

        switch ($choice) {
            case 1:
                return $this->interactiveCreateKey();
            case 2:
                return $this->interactiveRevokeKey();
            case 3:
                return $this->interactiveRevokeAllKeys();
            default:
                $this->info('Operação cancelada.');

                return 0;
        }
    }

    private function interactiveCreateKey(): int
    {
        $user = $this->chooseActiveUser();

        if (! $user) {
            return 1;
        }

        return $this->createKey($user, true);
    }

    private function chooseActiveUser(): ?User
    {
        $users = User::query()
            ->where('status', true)
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        if ($users->isEmpty()) {
            $this->error('Não há usuários ativos disponíveis para receber uma chave.');

            return null;
        }

        $choices = [];
        $usersByChoice = [];

        foreach ($users as $index => $user) {
            $choice = (string) ($index + 1);
            $choices[$choice] = "{$user->name} <{$user->email}>";
            $usersByChoice[$choice] = $user;
        }

        $selected = $this->choiceIndex('Selecione o usuário:', $choices, '1');

        return $usersByChoice[$selected];
    }

    private function createKey(?User $selectedUser = null, bool $confirm = false): int
    {
        if ($this->option('all')) {
            $this->error('A opção --all só pode ser usada ao revogar chaves.');

            return 1;
        }

        $email = trim((string) $this->argument('identifier'));
        $user = $selectedUser;

        if (! $user && $email === '') {
            $this->error('Informe o e-mail do usuário para criar a chave.');

            return 1;
        }

        $name = trim((string) $this->option('name'));
        if ($name === '' && $this->input->isInteractive()) {
            $name = trim((string) $this->ask('Nome da chave', $selectedUser ? 'Integração' : null));
        }

        if ($name === '') {
            $this->error('Informe o nome da chave com --name.');

            return 1;
        }

        if (Str::length($name) > 255) {
            $this->error('O nome da chave deve ter no máximo 255 caracteres.');

            return 1;
        }

        $user = $user ?: User::where('email', $email)->first();
        if (! $user) {
            $this->error("Nenhum usuário encontrado para o e-mail '{$email}'.");

            return 1;
        }

        if (! $user->status) {
            $this->error('Não é possível criar uma chave para um usuário inativo.');

            return 1;
        }

        if ($confirm && ! $this->confirm("Criar a chave \"{$name}\" para {$user->email}?", true)) {
            $this->info('Operação cancelada.');

            return 1;
        }

        $newToken = $user->createToken($name);

        $this->info('Chave de API criada com sucesso.');
        $this->line("ID: {$newToken->accessToken->getKey()}");
        $this->line("Usuário: {$user->email}");
        $this->line("Nome: {$name}");
        $this->line("Chave: {$newToken->plainTextToken}");
        $this->warn('Guarde esta chave agora: o segredo não será exibido novamente.');

        return 0;
    }

    private function interactiveRevokeKey(): int
    {
        $tokenModel = Sanctum::personalAccessTokenModel();
        $tokens = $tokenModel::query()
            ->with('tokenable')
            ->orderByDesc('created_at')
            ->get();

        if ($tokens->isEmpty()) {
            $this->error('Não há chaves de API para revogar.');

            return 1;
        }

        $choices = [];
        $tokensByChoice = [];

        foreach ($tokens as $index => $token) {
            $choice = (string) ($index + 1);
            $choices[$choice] = $this->tokenLabel($token);
            $tokensByChoice[$choice] = $token;
        }

        $selected = $this->choiceIndex('Selecione a chave para revogar:', $choices, '1');

        return $this->revokeToken($tokensByChoice[$selected]);
    }

    private function interactiveRevokeAllKeys(): int
    {
        $users = User::query()
            ->whereHas('tokens')
            ->withCount('tokens')
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        if ($users->isEmpty()) {
            $this->error('Não há usuários com chaves de API para revogar.');

            return 1;
        }

        $choices = [];
        $usersByChoice = [];

        foreach ($users as $index => $user) {
            $choice = (string) ($index + 1);
            $choices[$choice] = "{$user->name} <{$user->email}> — {$user->tokens_count} chave(s)";
            $usersByChoice[$choice] = $user;
        }

        $selected = $this->choiceIndex('Selecione o usuário:', $choices, '1');

        return $this->revokeAllKeys($usersByChoice[$selected]->email);
    }

    private function choiceIndex(string $question, array $choices, string $default): int
    {
        $selected = $this->choice($question, $choices, $default);
        $index = array_search($selected, $choices, true);

        if ($index === false) {
            throw new \LogicException("A opção selecionada em '{$question}' não foi encontrada.");
        }

        return (int) $index;
    }

    private function tokenLabel($token): string
    {
        $user = $token->tokenable;
        $owner = $user ? "{$user->name} <{$user->email}>" : 'Usuário removido';
        $createdAt = $token->created_at ? $token->created_at->format('d/m/Y H:i') : '-';
        $lastUsedAt = $token->last_used_at ? $token->last_used_at->format('d/m/Y H:i') : 'nunca';

        return "#{$token->getKey()} | {$owner} | {$token->name} | criada {$createdAt} | último uso {$lastUsedAt}";
    }

    private function revokeKey(): int
    {
        $identifier = trim((string) $this->argument('identifier'));

        if ($this->option('all')) {
            return $this->revokeAllKeys($identifier);
        }

        if ($identifier === '' || ! ctype_digit($identifier) || (int) $identifier < 1) {
            $this->error('Informe o ID numérico da chave para revogá-la.');

            return 1;
        }

        $tokenModel = Sanctum::personalAccessTokenModel();
        $token = $tokenModel::query()->find((int) $identifier);

        if (! $token) {
            $this->error("Nenhuma chave encontrada com o ID {$identifier}.");

            return 1;
        }

        return $this->revokeToken($token);
    }

    private function revokeToken($token): int
    {
        if (! $this->confirmRevocation("Revogar a chave #{$token->getKey()} ({$token->name})?")) {
            return 1;
        }

        $token->delete();
        $this->info("Chave #{$token->getKey()} revogada com sucesso.");

        return 0;
    }

    private function revokeAllKeys(string $email): int
    {
        if ($email === '') {
            $this->error('Informe o e-mail do usuário ao usar --all.');

            return 1;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("Nenhum usuário encontrado para o e-mail '{$email}'.");

            return 1;
        }

        $tokens = $user->tokens();
        $count = $tokens->count();

        if ($count === 0) {
            $this->info('O usuário não possui chaves de API ativas.');

            return 0;
        }

        if (! $this->confirmRevocation("Revogar todas as {$count} chave(s) de {$email}?")) {
            return 1;
        }

        $tokens->delete();
        $this->info("{$count} chave(s) de API revogada(s) com sucesso.");

        return 0;
    }

    private function confirmRevocation(string $question): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            $this->error('A revogação não interativa exige a opção --force.');

            return false;
        }

        if (! $this->confirm($question)) {
            $this->info('Operação cancelada.');

            return false;
        }

        return true;
    }
}
