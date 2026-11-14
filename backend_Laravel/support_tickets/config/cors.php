<?php

return [
    // Configuration CORS complète pour garantir la communication frontend-backend
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    
    // Origines autorisées : liste explicite, configurable via CORS_ALLOWED_ORIGINS (séparées par des virgules)
    'allowed_origins' => array_filter(array_map('trim', explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:3000,http://127.0.0.1:3000'
    )))),

    // Pas de pattern générique : toute origine doit être listée explicitement
    'allowed_origins_patterns' => [],

    // En-têtes autorisés pour les requêtes
    'allowed_headers' => [
        'X-CSRF-TOKEN',
        'X-Requested-With',
        'Content-Type',
        'Content-Length',
        'Authorization',
        'Accept',
        'X-Auth-Token',
        'Origin',
        'Cache-Control',
        'Pragma'
    ],
    
    // En-têtes exposés dans les réponses
    'exposed_headers' => ['Cache-Control', 'Content-Language', 'Content-Type', 'Expires', 'Last-Modified', 'Pragma'],
    
    // Durée de mise en cache des pré-requêtes CORS (en secondes)
    'max_age' => 3600,
    
    // Autoriser l'envoi de cookies avec les requêtes (crucial pour Sanctum)
    'supports_credentials' => true,
];
