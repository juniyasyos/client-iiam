<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Environment File Directory
    |--------------------------------------------------------------------------
    |
    | All environment definitions live inside this directory. Each environment
    | file overrides the shared configuration stored in environments/base.env.
    | The Artisan command will look up files here when switching environments.
    |
    */

    'directory' => base_path('environments'),

    /*
    |--------------------------------------------------------------------------
    | Registered Environments
    |--------------------------------------------------------------------------
    |
    | Describes the environments that can be activated through the custom
    | Artisan command. The "file" entry is expected to exist within the
    | directory defined above.
    |
    */

    'environments' => [
        'local' => [
            'label' => 'Local',
            'file' => 'local.env',
            'description' => 'Local development machine defaults.',
        ],
        'development' => [
            'label' => 'Development',
            'file' => 'development.env',
            'description' => 'Shared development or staging environment.',
        ],
        'production' => [
            'label' => 'Production',
            'file' => 'production.env',
            'description' => 'Production-ready configuration.',
        ],
    ],
];
