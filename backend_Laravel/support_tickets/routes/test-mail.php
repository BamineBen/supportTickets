<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function() {
    try {
        Mail::raw('Test d\'envoi d\'email depuis Laravel pour le système de tickets de support', function($message) {
            $message->to('logitechs629@gmail.com')
                   ->subject('Test de configuration email Laravel');
        });
        
        return 'Email envoyé avec succès ! Vérifiez votre boîte de réception.';
    } catch (\Exception $e) {
        return 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage();
    }
});
