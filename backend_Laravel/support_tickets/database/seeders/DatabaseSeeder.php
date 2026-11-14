<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crée les permissions et rôles en premier
        $this->call([
            PermissionSeeder::class,
        ]);

        // Crée ou met à jour l'utilisateur admin principal
        // Compte admin initial : identifiants lus depuis le .env, jamais écrits dans le code.
        $adminEmail = env('SEED_ADMIN_EMAIL');
        $adminPassword = env('SEED_ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('SEED_ADMIN_NAME', 'Administrateur'),
                    'password' => bcrypt($adminPassword),
                    'email_verified_at' => now(),
                ]
            );
            $admin->syncRoles(['admin']);
        } else {
            $this->command?->warn('SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD absents : aucun admin créé.');
        }

        // Données de démonstration (mots de passe connus) : jamais en production
        if (app()->environment('local', 'development', 'testing')) {
            $this->call([
                UserSeeder::class,
                TicketSeeder::class,
                CommentaireSeeder::class,
            ]);
        }
    }
}
