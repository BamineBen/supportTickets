<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Exécute le seeder pour créer des utilisateurs de test.
     *
     * @return void
     */
    public function run(): void
    {
        // Créer 10 utilisateurs standard
        $users = [
            [
                'name' => 'Sophie Martin',
                'email' => 'sophie.martin@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'Thomas Bernard',
                'email' => 'thomas.bernard@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'Emilie Dubois',
                'email' => 'emilie.dubois@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'Lucas Robert',
                'email' => 'lucas.robert@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'Julie Petit',
                'email' => 'julie.petit@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'David Leroy',
                'email' => 'david.leroy@example.com',
                'password' => 'password123',
                'role' => 'admin'
            ]
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now()
                ]
            );
            
            // Assigner le rôle spécifié
            $user->syncRoles([$userData['role']]);
        }
    }
}
