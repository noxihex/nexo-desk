<?php

return [

    'enabled' => env('AUDITING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Audit Implementation
    |--------------------------------------------------------------------------
    |
    | Define which Audit model implementation should be used.
    |
    */
    'implementation' => OwenIt\Auditing\Models\Audit::class,

    /*
    |--------------------------------------------------------------------------
    | User Morph prefix & Guards
    |--------------------------------------------------------------------------
    |
    | Define the morph prefix and authentication guards for the User resolver.
    |
    */
    'user' => [
        'morph_prefix' => 'user',
        'guards' => ['web'],  // Apenas o guard web será auditado
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Resolvers
    |--------------------------------------------------------------------------
    |
    | Define the IP Address, User Agent and URL resolver implementations.
    |
    */
    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Events
    |--------------------------------------------------------------------------
    |
    | The Eloquent events that trigger an Audit.
    |
    */
    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',  // Inclui restaurações, se aplicável
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | Enable the strict mode when auditing?
    |
    */
    'strict' => false,

    /*
    |--------------------------------------------------------------------------
    | Global exclude
    |--------------------------------------------------------------------------
    |
    | Specify fields to exclude globally from all audit records.
    |
    */
    'exclude' => [
        'remember_token',  // Exclui tokens de sessão de auditoria
    ],

    /*
    |--------------------------------------------------------------------------
    | Empty Values
    |--------------------------------------------------------------------------
    |
    | Should Audit records be stored when the recorded old_values & new_values
    | are both empty?
    |
    */
    'empty_values' => false,

    /*
    |--------------------------------------------------------------------------
    | Allowed Array Values
    |--------------------------------------------------------------------------
    |
    | Should the array values be audited? This is disabled for performance reasons.
    |
    */
    'allowed_array_values' => false,

    /*
    |--------------------------------------------------------------------------
    | Audit Timestamps
    |--------------------------------------------------------------------------
    |
    | Should the created_at, updated_at and deleted_at timestamps be audited?
    |
    */
    'timestamps' => true,  // Para registrar horários de criação e modificação

    /*
    |--------------------------------------------------------------------------
    | Audit Threshold
    |--------------------------------------------------------------------------
    |
    | Specify a threshold for the amount of Audit records a model can have.
    | Zero means no limit.
    |
    */
    'threshold' => 0,  // Sem limite de registros de auditoria por modelo

    /*
    |--------------------------------------------------------------------------
    | Audit Driver
    |--------------------------------------------------------------------------
    |
    | The default audit driver used to keep track of changes.
    |
    */
    'driver' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Audit Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Available audit drivers and respective configurations.
    |
    */
    'drivers' => [
        'database' => [
            'table' => 'audits',  // Nome da tabela de auditoria
            'connection' => null,  // Usa a conexão padrão
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Queue Configurations
    |--------------------------------------------------------------------------
    |
    | Enable queue for audit records if performance is a concern.
    |
    */
    'queue' => [
        'enable' => false,  // Pode habilitar para grandes volumes de registros
        'connection' => 'sync',
        'queue' => 'default',
        'delay' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Console
    |--------------------------------------------------------------------------
    |
    | Whether console events should be audited (e.g., php artisan commands).
    |
    */
    'console' => false,
];
