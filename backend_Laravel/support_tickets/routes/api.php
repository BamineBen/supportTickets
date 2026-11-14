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
    ]);
});

// Routes publiques pour l'authentification
// Limitation de débit pour freiner le brute-force et le spam
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // URL d'accès: http://localhost:8000/api/login
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('throttle:10,1');

// Route pour le formulaire de contact avec reCAPTCHA
Route::post('/contact', [App\Http\Controllers\API\ContactController::class, 'send'])->middleware('throttle:5,1');


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

// Routes pour la gestion des utilisateurs
// Toutes ces routes sont réservées aux administrateurs (vérifié dans le contrôleur)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('api.users.show');

    // Routes réservées aux administrateurs - utilisation d'une vérification manuelle
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);
});