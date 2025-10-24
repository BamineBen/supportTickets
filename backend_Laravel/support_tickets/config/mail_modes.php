<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mail Driver Mode
    |--------------------------------------------------------------------------
    |
    | Cette configuration permet de basculer facilement entre SMTP standard 
    | et Mailtrap pour les tests en développement.
    |
    */

    'mode' => env('MAIL_DRIVER_MODE', 'smtp'),
    
    'mailtrap' => [
        'host' => env('MAILTRAP_HOST', 'smtp.mailtrap.io'),
        'port' => env('MAILTRAP_PORT', 2525),
        'username' => env('MAILTRAP_USERNAME'),
        'password' => env('MAILTRAP_PASSWORD'),
        'encryption' => env('MAILTRAP_ENCRYPTION', null),
    ],
];
