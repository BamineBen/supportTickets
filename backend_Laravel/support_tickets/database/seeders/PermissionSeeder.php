<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Exécute le seeder pour créer les permissions et rôles.
     *
     * @return void
     */
    public function run(): void
    {
        // Création des rôles - On utilise 'user' pour être cohérent avec le reste de l'application
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Liste des permissions pour le système de gestion de tickets (en français)
        $permissions = [
            'voir_tous_tickets',     // Voir tous les tickets (admin)
            'gerer_tous_tickets',    // Gérer tous les tickets (admin)
            'creer_ticket',          // Créer un nouveau ticket
            'modifier_ticket',       // Modifier son propre ticket
            'supprimer_ticket',      // Supprimer son propre ticket
            'commenter_ticket',      // Commenter un ticket
            'voir_utilisateurs',     // Voir la liste des utilisateurs
            'gerer_utilisateurs',    // Gérer les utilisateurs (créer, modifier, supprimer)
            'modifier_roles',        // Modifier les rôles des utilisateurs
            'voir_dashboard',        // Accéder au tableau de bord
            'voir_stats_avancees',   // Voir les statistiques avancées (admin)
        ];

        // Crée chaque permission
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Donner toutes les permissions à l'admin
        $adminRole->givePermissionTo(Permission::all());

        // Donner les permissions limitées à l'utilisateur
        $userRole->givePermissionTo([
            'creer_ticket',
            'modifier_ticket',
            'supprimer_ticket',
            'commenter_ticket',
            'voir_dashboard'
        ]);

        /* Posibilité d'évolution en tant que nouveau rôle (agent)
        $agentRole = Role::create(['name' => 'agent']);
        $agentRole->givePermissionTo([
            'voir_tous_tickets',
            'commenter_ticket',
            'gerer_tous_tickets',
            'voir_dashboard'
        ]);
        */
    }
}
