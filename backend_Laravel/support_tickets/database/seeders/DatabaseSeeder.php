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
        $admin = User::updateOrCreate(
            ['email' => 'bamine@gmail.com'], // Clé unique pour éviter les doublons
            [
                'name' => 'Bamine Admin',
                'password' => bcrypt('secure_password_123'), // Remplace par un mot de passe fort
                'email_verified_at' => now(),
            ]
        );

        // Assigne uniquement le rôle admin (supprime les autres rôles)
        $admin->syncRoles(['admin']);

        // Crée ou met à jour l'utilisateur test standard
        $user = User::updateOrCreate(
            ['email' => 'test@gmail.com'], // Clé unique pour éviter les doublons
            [
                'name' => 'test user',
                'password' => bcrypt('secure_password_123'), // Remplace par un mot de passe fort
                'email_verified_at' => now(),
            ]
        );

        // Assigne uniquement le rôle utilisateur standard
        $user->syncRoles(['user']);
        
        // Crée des utilisateurs supplémentaires, des tickets et des commentaires pour les tests
        if (app()->environment('local', 'development', 'testing')) {
            $this->call([
                UserSeeder::class,
                TicketSeeder::class,
                CommentaireSeeder::class,
            ]);
        }
    }
}
