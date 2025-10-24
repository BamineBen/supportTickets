<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="API d'authentification pour la gestion des comptes utilisateurs"
 * )
 */

class AuthController extends Controller
{
    /**
     * Login et génération de token API
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authentifier un utilisateur et générer un token",
     *     description="Authentifie un utilisateur avec son email et mot de passe et génère un token API",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentification réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Identifiants incorrects")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Vérifier d'abord les identifiants
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->email)->first();
            
            // Vérifier si l'email est vérifié
            if ($user->email_verified_at === null) {
                return response()->json([
                    'message' => 'Veuillez vérifier votre adresse email avant de vous connecter.',
                    'email_verified' => false
                ], 403);
            }
            
            $token = $user->createToken('api-token')->plainTextToken;

            // Inclure les rôles de l'utilisateur dans la réponse
            $roles = $user->getRoleNames();

            return response()->json([
                'user' => $user,
                'roles' => $roles,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'email_verified' => true
            ]);
        }

        return response()->json([
            'message' => 'Identifiants incorrects',
        ], 401);
    }

    /**
     * Inscription d'un nouvel utilisateur
     *
     * @OA\Post(
     *     path="/api/register",
     *     summary="Inscription d'un nouvel utilisateur",
     *     description="Crée un nouvel utilisateur avec le rôle 'user' par défaut",
     *     operationId="register",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="message", type="string", example="Inscription réussie, veuillez vérifier votre adresse e-mail.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        // Attribue le rôle "user" si tu utilises spatie/laravel-permission
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('user');
        }

        // Nous utilisons une approche différente pour éviter le double envoi d'emails
        // L'email sera envoyé directement plutôt que via un événement
        $user->sendEmailVerificationNotification();
        // Envoie le mail de vérification
        event(new \Illuminate\Auth\Events\Registered($user));

        // Retourne une réponse JSON au frontend
        return response()->json([
            'user' => $user,
            'message' => 'Inscription réussie. Veuillez vérifier votre email.'
        ], 201);
    }

    /**
     * Récupération des informations de l'utilisateur courant
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $roles = $user->getRoleNames();

        return response()->json([
            'user' => $user,
            'roles' => $roles
        ]);
    }
    
    /**
     * Récupération du profil de l'utilisateur connecté
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames()
        ]);
    }
    
    /**
     * Mise à jour du profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        
        $user->name = $request->name;
        $user->save();
        
        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
    }
    
    /**
     * Mise à jour du mot de passe de l'utilisateur connecté
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        
        $user = $request->user();
        
        // Vérification que l'ancien mot de passe est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect',
                'errors' => [
                    'current_password' => ['Le mot de passe actuel ne correspond pas à nos enregistrements.']
                ]
            ], 422);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    }

    /**
     * Déconnexion (révocation du token)
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnexion de l'utilisateur",
     *     description="Révoque le token d'accès actuel",
     *     operationId="logout",
     *     tags={"Authentification"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Demande de réinitialisation de mot de passe
     *
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Demande de réinitialisation de mot de passe",
     *     description="Envoie un email avec un lien de réinitialisation de mot de passe",
     *     operationId="forgotPassword",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email de réinitialisation envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Nous avons envoyé par e-mail votre lien de réinitialisation de mot de passe.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __($status)
        ]);
    }

    /**
     * Réinitialisation du mot de passe
     *
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Réinitialisation du mot de passe",
     *     description="Réinitialise le mot de passe avec le token fourni",
     *     operationId="resetPassword",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Votre mot de passe a été réinitialisé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => true, 'message' => __($status)]);
    }

    /**
     * Vérification de l'adresse email
     *
     * @OA\Post(
     *     path="/api/verify-email",
     *     summary="Vérification de l'adresse email",
     *     description="Vérifie l'adresse email de l'utilisateur avec l'ID et le hash fournis",
     *     operationId="verifyEmail",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id","hash"},
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="hash", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Adresse email vérifiée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Adresse e-mail vérifiée avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lien de vérification invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lien de vérification invalide")
     *         )
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric',
            'hash' => 'required|string',
        ]);

        $user = \App\Models\User::findOrFail($request->id);

        if (sha1($user->getEmailForVerification()) !== $request->hash) {
            return response()->json(['message' => 'Lien de vérification invalide.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié.'], 200);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email vérifié avec succès.'], 200);
    }
}
