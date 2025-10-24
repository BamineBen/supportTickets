<?php

namespace Database\Seeders;

use App\Models\Commentaire;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CommentaireSeeder extends Seeder
{
    /**
     * Exécute le seeder pour créer des commentaires de test.
     *
     * @return void
     */
    public function run(): void
    {
        // Récupérer tous les tickets
        $tickets = Ticket::all();

        if ($tickets->isEmpty()) {
            $this->command->info('Aucun ticket trouvé pour ajouter des commentaires. Exécutez d\'abord TicketSeeder.');
            return;
        }

        // Récupérer tous les utilisateurs
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur trouvé pour créer des commentaires. Exécutez d\'abord UserSeeder.');
            return;
        }

        // Liste de commentaires génériques pour différentes situations
        $commentairesUtilisateur = [
            'Pouvez-vous me donner une mise à jour sur ce problème ?',
            'Merci pour votre aide, je vais essayer cette solution.',
            'J\'ai essayé ce que vous m\'avez suggéré mais ça ne fonctionne toujours pas.',
            'Y a-t-il une autre solution possible ?',
            'Le problème persiste, pouvez-vous m\'aider davantage ?',
            'Je vous remercie pour votre réponse rapide.',
            'J\'ai trouvé des informations supplémentaires qui pourraient aider.',
            'Est-ce que vous pourriez me contacter par téléphone pour discuter de ce problème ?',
            'Je ne comprends pas bien la solution proposée.',
            'Problème résolu ! Merci pour votre aide.'
        ];

        $commentairesAdmin = [
            'Nous allons examiner ce problème dès que possible.',
            'Pouvez-vous nous fournir plus d\'informations pour nous aider à résoudre ce problème ?',
            'Avez-vous essayé de [solution spécifique] ?',
            'Ce problème est en cours d\'investigation par notre équipe technique.',
            'Nous avons identifié la cause du problème et travaillons sur une solution.',
            'Une mise à jour sera déployée prochainement pour résoudre ce problème.',
            'Pouvez-vous vérifier si le problème persiste après avoir effacé votre cache ?',
            'Nous avons besoin de plus de détails sur votre environnement pour reproduire ce bug.',
            'Ce comportement est attendu en raison de [explication technique].',
            'Problème résolu dans la dernière version, veuillez mettre à jour votre application.'
        ];

        // Pour chaque ticket, ajouter entre 0 et 5 commentaires
        foreach ($tickets as $ticket) {
            $nbCommentaires = rand(0, 5);
            
            for ($i = 0; $i < $nbCommentaires; $i++) {
                // Alterner entre utilisateur standard et admin/support pour les commentaires
                $isUserComment = $i % 2 == 0;
                
                // Sélectionner l'auteur du commentaire
                $user = null;
                if ($isUserComment) {
                    // Si c'est un commentaire utilisateur, utiliser le créateur du ticket
                    $user = $ticket->user;
                } else {
                    // Sinon, choisir un admin ou agent de support
                    $admins = $users->filter(function ($user) {
                        return $user->hasRole('admin') || $user->hasRole('agent_support');
                    });
                    
                    if ($admins->isNotEmpty()) {
                        $user = $admins->random();
                    } else {
                        $user = $users->random(); // Fallback si pas d'admin
                    }
                }
                
                // Sélectionner un commentaire approprié
                $contenu = $isUserComment
                    ? $commentairesUtilisateur[array_rand($commentairesUtilisateur)]
                    : $commentairesAdmin[array_rand($commentairesAdmin)];
                
                // Créer le commentaire avec une date antérieure à aujourd'hui
                Commentaire::create([
                    'contenu' => $contenu,
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 10))->subHours(rand(1, 23)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 10))->subHours(rand(1, 23))
                ]);
            }
        }
    }
}
