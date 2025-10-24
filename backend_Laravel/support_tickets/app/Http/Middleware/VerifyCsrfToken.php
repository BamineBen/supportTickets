<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login',
        '/login',
        'api/login',
        '/api/login',
        '*login*',
        // Vous pouvez ajouter d'autres routes API ici si nécessaire
        // 'api/*'
    ];
}
