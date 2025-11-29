<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Admin User Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the default admin user created during first-time
    | installation. You should set these in your .env file and change the
    | password immediately after first login.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@example.com'),

    'password' => env('ADMIN_PASSWORD', 'password'),

    'mobile' => env('ADMIN_MOBILE', ''),

    'organisation' => env('ADMIN_ORGANISATION', 'Summa'),

];
