<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TicketSeeder extends Seeder
{
    /**
     * Exécute le seeder pour créer des tickets de test.
     *
     * @return void
     */
    public function run(): void
    {
        // Récupérer tous les utilisateurs sauf les admins pour créer des tickets
        $users = User::whereHas('roles', function($q) {
            $q->where('name', '!=', 'admin');
        })->get();

        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur trouvé pour créer des tickets. Exécutez d\'abord UserSeeder.');
            return;
        }

        // Liste de tickets prédéfinis avec différents statuts et deadlines
        $ticketsData = [
            [
                'titre' => 'Problème de connexion à l\'application',
                'description' => 'Je ne parviens pas à me connecter à l\'application depuis ce matin. J\'ai essayé de réinitialiser mon mot de passe mais je ne reçois pas le mail de réinitialisation.',
                'statut' => 'ouvert',
                'deadline_days' => 2
            ],
            [
                'titre' => 'Erreur lors de la création d\'un nouveau projet',
                'description' => 'Quand je tente de créer un nouveau projet, j\'obtiens une erreur 500. J\'ai vérifié que tous les champs obligatoires sont bien remplis.',
                'statut' => 'en cours',
                'deadline_days' => 5
            ],
            [
                'titre' => 'Demande d\'une nouvelle fonctionnalité',
                'description' => 'Serait-il possible d\'ajouter une option pour exporter les rapports en format PDF? Cette fonctionnalité nous serait très utile pour nos présentations mensuelles.',
                'statut' => 'ouvert',
                'deadline_days' => 14
            ],
            [
                'titre' => 'Bug dans le module de statistiques',
                'description' => 'Les statistiques n\'affichent pas correctement les données du dernier mois. Les chiffres semblent incorrects par rapport à nos rapports internes.',
                'statut' => 'en cours',
                'deadline_days' => 3
            ],
            [
                'titre' => 'Problème d\'affichage sur mobile',
                'description' => 'Le tableau de bord ne s\'adapte pas correctement sur les écrans mobiles. Certains éléments dépassent de l\'écran et rendent l\'application inutilisable.',
                'statut' => 'ouvert',
                'deadline_days' => 7
            ],
            [
                'titre' => 'Demande d\'accès supplémentaire',
                'description' => 'J\'aurais besoin d\'un accès au module de reporting pour mon équipe. Nous sommes 5 personnes qui avons besoin de ces informations.',
                'statut' => 'fermé',
                'deadline_days' => -5 // Passé
            ],
            [
                'titre' => 'Lenteur de chargement des pages',
                'description' => 'Depuis la dernière mise à jour, les pages mettent beaucoup plus de temps à charger, en particulier le tableau de bord et les rapports.',
                'statut' => 'en cours',
                'deadline_days' => 1
            ],
            [
                'titre' => 'Question sur la facturation',
                'description' => 'Je ne comprends pas certains éléments de ma dernière facture. Pouvez-vous m\'expliquer pourquoi il y a eu une augmentation ce mois-ci?',
                'statut' => 'fermé',
                'deadline_days' => -10 // Passé
            ],
            [
                'titre' => 'Problème avec l\'intégration API',
                'description' => 'Notre système ne parvient pas à se connecter à votre API. Nous obtenons systématiquement une erreur d\'authentification malgré l\'utilisation des bons credentials.',
                'statut' => 'ouvert',
                'deadline_days' => 2
            ],
            [
                'titre' => 'Suggestion d\'amélioration UX',
                'description' => 'Il serait plus intuitif que le bouton de validation soit toujours visible en bas de page, même lors du défilement. Cela éviterait de devoir remonter pour valider un formulaire.',
                'statut' => 'en cours',
                'deadline_days' => 10
            ],
            [
                'titre' => 'Erreur lors de l\'export des données',
                'description' => 'Quand j\'essaie d\'exporter les données en CSV, le fichier généré est corrompu et ne peut pas être ouvert correctement.',
                'statut' => 'ouvert',
                'deadline_days' => 4
            ],
            [
                'titre' => 'Demande de formation',
                'description' => 'Nous avons récemment recruté de nouveaux collaborateurs qui auraient besoin d\'une formation sur l\'utilisation de la plateforme. Est-il possible d\'organiser une session?',
                'statut' => 'fermé',
                'deadline_days' => -15 // Passé
            ],
            [
                'titre' => 'Problème de notification email',
                'description' => 'Je ne reçois plus les notifications par email lorsqu\'un nouveau ticket est créé ou mis à jour. J\'ai vérifié mes paramètres et tout semble correct.',
                'statut' => 'ouvert',
                'deadline_days' => 3
            ],
            [
                'titre' => 'Besoin d\'assistance pour configuration',
                'description' => 'Nous essayons de configurer le système pour notre nouvelle équipe mais nous rencontrons des difficultés. Pouvez-vous nous aider ou nous fournir une documentation plus détaillée?',
                'statut' => 'en cours',
                'deadline_days' => 6
            ],
            [
                'titre' => 'Suggestion de nouvelle interface',
                'description' => 'L\'interface actuelle est un peu datée. Serait-il possible de la moderniser et d\'ajouter un mode sombre qui est plus agréable pour travailler en soirée?',
                'statut' => 'ouvert',
                'deadline_days' => 20
            ]
        ];

        // Créer les tickets avec des utilisateurs aléatoires
        foreach ($ticketsData as $ticketData) {
            $user = $users->random();
            
            // Calculer la deadline en fonction du nombre de jours
            $deadline = null;
            if (isset($ticketData['deadline_days'])) {
                $deadline = Carbon::now()->addDays($ticketData['deadline_days']);
            }
            
            Ticket::create([
                'titre' => $ticketData['titre'],
                'description' => $ticketData['description'],
                'statut' => $ticketData['statut'],
                'user_id' => $user->id,
                'deadline' => $deadline,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 15))
            ]);
        }
    }
}
