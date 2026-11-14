<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Rediriger l'utilisateur vers Google pour l'authentification
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        try {
            // Définir explicitement les scopes requis
            $scopes = ['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile'];
            
            // Paramètres supplémentaires pour forcer le consentement
            $parameters = ['access_type' => 'offline', 'prompt' => 'consent select_account', 'response_type' => 'code'];
            
            // Désactiver SSL en mode développement si nécessaire
            $googleDriver = Socialite::driver('google')
                ->scopes($scopes)
                ->with($parameters);
                
            if (app()->environment('local', 'development')) {
                $googleDriver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            
            return $googleDriver->stateless()->redirect();
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la redirection vers Google: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Impossible de se connecter à Google. Veuillez réessayer plus tard.');
        }
    }

    /**
     * Obtenir les informations de l'utilisateur depuis Google
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleCallback()
    {
        try {
            // Désactiver SSL en mode développement si nécessaire
            $googleDriver = Socialite::driver('google')->stateless();
            
            if (app()->environment('local', 'development')) {
                $googleDriver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            
            // Récupérer les informations de l'utilisateur Google
            $googleUser = $googleDriver->user();
            
            // Journaliser pour déboguer
            \Log::info('Callback Google réussi, email: ' . $googleUser->email);

            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', $googleUser->email)->first();

            // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
            if (!$user) {
                \Log::info('Création d\'un nouvel utilisateur: ' . $googleUser->email);
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(40)), // Mot de passe aléatoire
                    'email_verified_at' => now(), // Marquer l'email comme vérifié
                ]);

                // Assigner le rôle 'user' par défaut
                $user->assignRole('user');
            }

            // Générer un token API
            $token = $user->createToken('google-auth')->plainTextToken;
            \Log::info('Token généré pour l\'utilisateur: ' . $user->id);

            // Rediriger vers le frontend avec le token en paramètre
            return redirect(rtrim(config('app.frontend_url'), '/') . '/auth/google/callback?token=' . $token);

        } catch (\Exception $e) {
            // Journaliser l'erreur pour déboguer
            \Log::error('Erreur dans handleGoogleCallback: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            // Rediriger vers la page de login avec un message d'erreur
            return redirect(rtrim(config('app.frontend_url'), '/') . '/login?error=google_auth_failed');
        }
    }

    /**
     * Authentifier via Google pour l'API (pour les clients mobiles)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticateWithGoogle(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Vérifier le token ID avec Google
            $googleUser = Socialite::driver('google')->userFromToken($request->id_token);

            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', $googleUser->email)->first();

            // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(40)), // Mot de passe aléatoire
                ]);

                // Assigner le rôle 'user' par défaut
                $user->assignRole('user');
            }

            // Générer un token API
            $token = $user->createToken('google-auth')->plainTextToken;

            // Inclure les rôles de l'utilisateur dans la réponse
            $roles = $user->getRoleNames();

            return response()->json([
                'user' => $user,
                'roles' => $roles,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur authenticateWithGoogle: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur d\'authentification Google',
                'message' => 'Authentification Google impossible. Veuillez réessayer.'
            ], 401);
        }
    }
}
