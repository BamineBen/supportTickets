<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Traiter un message de contact avec validation reCAPTCHA et envoi d'email
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Validation des données du formulaire
        Log::info('[CONTACT] Début traitement formulaire', $request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
            'recaptchaToken' => 'required|string'
        ]);

        if ($validator->fails()) {
            Log::error('[CONTACT] Validation échouée', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Veuillez remplir correctement tous les champs du formulaire.',
                'errors' => $validator->errors()
            ], 422);
        }
        Log::info('[CONTACT] Validation OK');

        try {
            // Vérifier le token reCAPTCHA avec l'API Google (désactivation de la vérification SSL pour dev)
            $recaptchaResponse = Http::withOptions([
                'verify' => false  // Désactive la vérification SSL - À NE PAS UTILISER en production
            ])->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->recaptchaToken,
                'remoteip' => $request->ip()
            ]);

            $recaptchaData = $recaptchaResponse->json();
            
            // Log de la réponse complète de reCAPTCHA pour diagnostic
            Log::info('[CONTACT] Réponse reCAPTCHA complète:', $recaptchaData);
            
            // Vérification du token reCAPTCHA
            if (!isset($recaptchaData['success'])) {
                Log::error('[CONTACT] Clé success absente dans la réponse reCAPTCHA');
                return response()->json([
                    'success' => false,
                    'message' => 'La vérification reCAPTCHA a échoué (format de réponse invalide). Veuillez réessayer.'
                ], 400);
            }
            
            if ($recaptchaData['success'] !== true) {
                Log::error('[CONTACT] Échec de validation reCAPTCHA', [
                    'error_codes' => $recaptchaData['error-codes'] ?? 'Aucun code d\'erreur fourni'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'La vérification reCAPTCHA a échoué. Veuillez réessayer.'
                ], 400);
            }
            
            Log::info('[CONTACT] Vérification reCAPTCHA réussie');

            // Préparer le contenu du message
            $messageText = "Nouveau message de contact\n\n" .
                "Nom: {$request->name}\n" .
                "Email: {$request->email}\n" .
                "Sujet: {$request->subject}\n\n" .
                "Message:\n{$request->message}\n\n" .
                "Date: " . now()->format('d/m/Y H:i');

            // Déterminer l'adresse email de réception
            $contactEmail = env('CONTACT_EMAIL', 'logitechs629@gmail.com');

            // Log des infos de configuration SMTP pour debug
            $smtpConfig = [
                'MAIL_HOST' => config('mail.mailers.smtp.host'),
                'MAIL_PORT' => config('mail.mailers.smtp.port'),
                'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
                'MAIL_FROM_ADDRESS' => config('mail.from.address'),
                'MAIL_FROM_NAME' => config('mail.from.name'),
                'CONTACT_EMAIL' => $contactEmail
            ];
            Log::info('[CONTACT] Configuration SMTP', $smtpConfig);
            Log::info('[CONTACT] Tentative d\'envoi d\'email à: ' . $contactEmail . ' via Mailtrap');

            try {
                // Utiliser Mail::raw pour un email simple en texte brut
                Mail::raw($messageText, function($message) use ($contactEmail, $request) {
                    $message->to($contactEmail)
                        ->subject("Nouveau message de contact: {$request->subject}")
                        ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'Support Tickets'));
                    
                    // Ajouter un log pour s'assurer que les détails du message sont corrects
                    Log::info('[CONTACT] Détails du message', [
                        'to' => $contactEmail,
                        'subject' => "Nouveau message de contact: {$request->subject}",
                        'from' => config('mail.from.address', 'noreply@example.com')
                    ]);
                });
                Log::info('[CONTACT] Email envoyé AVEC SUCCÈS à ' . $contactEmail . ' via Mailtrap');
            } catch (\Exception $e) {
                Log::error('[CONTACT] ECHEC ENVOI EMAIL: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Erreur lors de l'envoi du mail de contact: " . $e->getMessage()
                ], 500);
            }

            Log::info('[CONTACT] Nouveau message de contact reçu', [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message_length' => strlen($request->message)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Votre message a bien été envoyé (visible dans votre boîte Mailtrap).'
            ]);

        } catch (\Exception $e) {
            // En cas d'erreur, logger le problème mais ne pas exposer les détails
            Log::error('Erreur lors du traitement du message de contact: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }
}
