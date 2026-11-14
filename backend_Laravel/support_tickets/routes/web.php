<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Gestion_tickets\TicketController;
use App\Http\Controllers\Gestion_tickets\CommentaireController;
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

    // La gestion des utilisateurs se fait via l'API (/api/users) et le frontend Vue.
});

// PAGE TEMPORAIRE de création d'admin (inactive sans ADMIN_SETUP_TOKEN, usage unique). À supprimer après usage.
Route::get('/setup-admin', [App\Http\Controllers\AdminSetupController::class, 'show'])->middleware('throttle:5,1');
Route::post('/setup-admin', [App\Http\Controllers\AdminSetupController::class, 'store'])->middleware('throttle:5,1');

require __DIR__.'/auth.php';
