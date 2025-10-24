@component('mail::message')
# Test de configuration d'emails

Ceci est un email de test pour vérifier la configuration de l'envoi d'emails dans l'application de Gestion de Tickets de Support.

**Mode utilisé**: {{ config('mail_modes.mode', 'smtp') }}

Si vous recevez cet email, cela signifie que la configuration fonctionne correctement.

@component('mail::button', ['url' => config('app.url')])
Retour à l'application
@endcomponent

Merci,<br>
L'équipe {{ config('app.name') }}
@endcomponent
