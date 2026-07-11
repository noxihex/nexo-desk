<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => env('APP_NAME_INICIO') . ' ' . env('APP_NAME_FINAL'),
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */


    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>' . env('APP_NAME_INICIO') . '</b> ' . env('APP_NAME_FINAL'),
    'logo_img' => 'vendor/adminlte/dist/img/logodesk.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => env('APP_NAME_INICIO') . ' ' . env('APP_NAME_FINAL'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/logodesk.png',
            'alt' => env('APP_NAME_INICIO') . ' ' . env('APP_NAME_FINAL'),
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/logodesk.png',
            'alt' => env('APP_NAME_INICIO') . ' ' . env('APP_NAME_FINAL'),
            'effect' => 'animation__shake',
            'width' => 80,
            'height' => 80,
        ],
    ],



    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => true,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    // Keep open submenus from leaking into the compact icon-only sidebar.
    'classes_sidebar_nav' => 'nav-collapse-hide-child',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => false,
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    // Keep AdminLTE's own Bootstrap, Font Awesome, jQuery and layout assets.
    // The BTX bundle is appended through the BtxTheme plugin below.
    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

'menu' => [
    // Item do Dashboard (sem submenu)
    [
        'text' => 'Visão Geral',
        'icon' => 'fas fa-fw fa-tachometer-alt',
        'url' => 'home',
    ],

    // Menu dos Tickets com submenu
    [
        'text' => 'Tickets',
        'icon' => 'fas fa-fw fa-ticket-alt',
        'submenu' => [
            [
                'text' => 'Criar novo ticket',
                'url' => 'tickets/create',
                'icon' => 'fas fa-fw fa-plus-circle',
                'can' => ['acesso admin', 'acesso analista', 'acesso supervisor'],
            ],
            [
                'text' => 'Criar novo ticket',
                'url' => 'tickets/cliente/create',
                'icon' => 'fas fa-fw fa-plus-circle',
                'can' => ['acesso cliente noc', 'acesso cliente dc'],
            ],
            [
                'text' => 'Meus tickets',
                'url' => 'tickets/cliente',
                'icon' => 'fas fa-fw fa-ticket-alt',
                'can' => ['acesso cliente noc', 'acesso cliente dc'],
            ],
            [
                'text' => 'Todos os Tickets',
                'url' => 'tickets',
                'can' => ['acesso admin', 'acesso analista', 'acesso supervisor'],
            ],
            [
                'text' => 'Tickets Pendentes',
                'url' => 'tickets/pendentes',
                'icon' => 'fas fa-fw fa-exclamation-circle',
                'can' => ['acesso admin', 'acesso analista', 'acesso supervisor'],
            ],
            [
                'text' => 'Meus Tickets',
                'url' => 'tickets/my',
                'icon' => 'fas fa-fw fa-user-check',
                'can' => ['acesso admin', 'acesso analista', 'acesso supervisor'],
            ],
        ],
    ],

    // Menu de Cadastros
    [
        'text' => 'Cadastros',
        'icon' => 'fas fa-fw fa-folder',
        'can' => ['acesso admin', 'acesso analista', 'acesso supervisor'],
        'submenu' => [
            [
                'text' => 'Usuários',
                'url' => 'cadastros/usuarios',
                'icon' => 'fas fa-fw fa-user-tie',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
            [
                'text' => 'Empresas',
                'url' => 'cadastros/empresas',
                'icon' => 'fas fa-fw fa-building',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
            [
                'text' => 'Setores',
                'url' => 'cadastros/setores',
                'icon' => 'fas fa-fw fa-sitemap',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
            [
                'text' => 'Categorias',
                'url' => 'cadastros/categorias',
                'icon' => 'fas fa-fw fa-tags',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
        ],
    ],

    // Menu Relatórios
    [
        'text' => 'Relatórios',
        'icon' => 'fas fa-fw fa-chart-bar',
        'can' => ['acesso admin', 'acesso supervisor'],
        'submenu' => [
            /*
            [
                'text' => 'Geral',
                'url' => 'relatorios/geral',
                'icon' => 'fas fa-fw fa-chart-pie',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
            */
            [
                'text' => 'Por analista',
                'url' => 'relatorios/analista',
                'icon' => 'fas fa-fw fa-user',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
            /*
            [
                'text' => 'Empresa',
                'url' => 'relatorios/empresa',
                'icon' => 'fas fa-fw fa-building',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
            */
            [
                'text' => 'Por empresa',
                'url' => 'relatorios/horas',
                'icon' => 'fas fa-business-time',
                'can' => ['acesso admin', 'acesso supervisor'],
            ],
        ],
    ],

    // Menu Administração (apenas para 'acesso admin')
    [
        'text' => 'Administração',
        'icon' => 'fas fa-fw fa-cogs',
        'can' => 'acesso admin',
        'submenu' => [
            [
                'text' => 'Usuários Logados',
                'url' => 'administracao/usuarioslogados',
                'icon' => 'fas fa-fw fa-user-clock',
                'can' => 'acesso admin',
            ],
            [
                'text' => 'Auditoria/Logs',
                'url' => 'administracao/auditoria',
                'icon' => 'fas fa-fw fa-clipboard-list',
                'can' => 'acesso admin',
            ],
            [
                'text' => 'Backup',
                'url' => 'administracao/backup',
                'icon' => 'fas fa-fw fa-database',
                'can' => 'acesso admin',
            ],
        ],
    ],

    // Item Minha conta
    [
        'text' => 'Minha conta',
        'icon' => 'fas fa-fw fa-user-cog',
        'url' => 'minhaconta',
    ],


],



    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'BtxTheme' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/app.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'js/app.js',
                ],
            ],
        ],
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
