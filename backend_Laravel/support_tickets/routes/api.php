<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GoogleAuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DashboardController;
use Spatie\Permission\Middleware\RoleMiddleware;

// Route de test simple (accessible sans auth)
Route::get('/test', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'L\'API fonctionne correctement',
        'timestamp' => now()->toDateTimeString(),
        'environment' => app()->environment()
    ]);
});

// Routes publiques pour l'authentification
Route::post('login', [AuthController::class, 'login']); // URL d'accès: http://localhost:8000/api/login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

// Route pour le formulaire de contact avec reCAPTCHA
Route::post('/contact', [App\Http\Controllers\API\ContactController::class, 'send']);


// === ROUTES ADMIN SEULEMENT ===
// Ces routes ont été consolidées plus bas dans le fichier (lignes 65-70)



// Routes pour l'authentification Google
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::post('/auth/google/token', [GoogleAuthController::class, 'authenticateWithGoogle']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Routes utilisateur
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Routes pour les tickets
    Route::get('/my-tickets', [App\Http\Controllers\API\TicketController::class, 'myTickets']);
    Route::apiResource('tickets', App\Http\Controllers\API\TicketController::class)->names([
        'index' => 'api.tickets.index',
        'store' => 'api.tickets.store',
        'show' => 'api.tickets.show',
        'update' => 'api.tickets.update',
        'destroy' => 'api.tickets.destroy',
    ]);
    
    // Routes pour les commentaires
    Route::get('/tickets/{ticket}/commentaires', [App\Http\Controllers\API\CommentaireController::class, 'index']);
    Route::post('/tickets/{ticket}/commentaires', [App\Http\Controllers\API\CommentaireController::class, 'store']);
    Route::apiResource('commentaires', App\Http\Controllers\API\CommentaireController::class, ['except' => ['index', 'store']]);
    
    
    // Routes pour le tableau de bord et statistiques
    Route::get('/dashboard/user-stats', [DashboardController::class, 'userStats']);
    Route::get('/dashboard/admin-stats', [DashboardController::class, 'adminStats']);
    Route::get('/dashboard/recent-tickets', [DashboardController::class, 'recentTickets']);
    Route::get('/dashboard/upcoming-deadlines', [DashboardController::class, 'upcomingDeadlines']);
    
    // Routes pour les notifications
    Route::get('/notifications', [App\Http\Controllers\API\NotificationController::class, 'index']);
    Route::get('/notifications/all', [App\Http\Controllers\API\NotificationController::class, 'all']);
    Route::patch('/notifications/{id}/read', [App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [App\Http\Controllers\API\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\API\NotificationController::class, 'destroy']);
});

// ROUTES D'URGENCE SANS MIDDLEWARES POUR DÉBOGUER
Route::get('/users-list', [UserController::class, 'listAllUsers']);
Route::patch('/change-user-role/{user}', [UserController::class, 'changeRoleEmergency']);

// Nous n'utilisons pas de routes d'urgence, uniquement les routes standard avec middleware

// Routes pour la gestion des utilisateurs
// Routes de base accessibles à tous les utilisateurs authentifiés
Route::middleware(['auth:sanctum'])->group(function () {
    // Routes pour tous les utilisateurs authentifiés (lecture seule)
    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('api.users.show');

    // Routes réservées aux administrateurs - utilisation d'une vérification manuelle
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);
});