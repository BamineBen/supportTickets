<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Gestion_tickets\TicketController;
use App\Http\Controllers\Gestion_tickets\CommentaireController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $tickets = Auth::user()->hasRole('admin')
        ? App\Models\Ticket::latest()->paginate(10)
        : Auth::user()->tickets()->latest()->paginate(10);

    return view('dashboard', compact('tickets'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Ticket.php
    Route::resource('tickets', TicketController::class);
    // Commentaires (réponses aux tickets)
    Route::post('tickets/{ticket}/commentaires', [CommentaireController::class, 'store'])
        ->name('commentaires.store');
});

// Routes réservées aux administrateurs
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Vue admin
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Gestion des utilisateurs (uniquement admin)
    Route::resource('users', UserController::class)
        ->only(['index', 'edit', 'update'])
        ->names([
            'index' => 'web.users.index',
            'edit' => 'web.users.edit',
            'update' => 'web.users.update'
        ]);
});

// Route de test pour l'envoi d'email
Route::get('/test-mail', function() {
    try {
        \Illuminate\Support\Facades\Mail::raw('Test d\'envoi d\'email depuis Laravel pour le système de tickets de support', function($message) {
            $message->to('logitechs629@gmail.com')
                   ->subject('Test de configuration email Laravel');
        });
        
        return 'Email envoyé avec succès ! Vérifiez votre boîte de réception.';
    } catch (\Exception $e) {
        return 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage();
    }
});

require __DIR__.'/auth.php';
