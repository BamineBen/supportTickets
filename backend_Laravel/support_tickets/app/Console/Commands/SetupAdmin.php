<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

/**
 * Formulaire interactif (terminal) pour créer un administrateur et retirer
 * les anciens comptes de test. Le mot de passe est saisi masqué : il n'est
 * jamais affiché, ni écrit dans le code, le .env ou les logs.
 *
 * Usage : php artisan admin:setup
 */
class SetupAdmin extends Command
{
    protected $signature = 'admin:setup';

    protected $description = 'Créer un administrateur (mot de passe saisi masqué) et supprimer les anciens comptes de test';

    /** Comptes créés par l'ancien seeder avec un mot de passe public. */
    private const OLD_TEST_ACCOUNTS = ['bamine@gmail.com', 'test@gmail.com'];

    public function handle(): int
    {
        $this->info('=== Création du compte administrateur ===');

        // Les rôles doivent exister (idempotent)
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $name = text(
            label: 'Nom affiché',
            default: 'Administrateur',
            required: true,
        );

        $email = text(
            label: 'Adresse email du nouvel admin',
            required: true,
            validate: function (string $value) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Adresse email invalide.';
                }
                if (in_array(strtolower($value), self::OLD_TEST_ACCOUNTS, true)) {
                    return 'Cette adresse est un ancien compte de test : choisissez-en une autre.';
                }
                return null;
            },
        );

        $existing = User::where('email', $email)->first();
        if ($existing && !confirm("Un compte {$email} existe déjà. Le promouvoir admin et remplacer son mot de passe ?", default: false)) {
            $this->warn('Annulé, rien n\'a été modifié.');
            return self::FAILURE;
        }

        $plain = password(
            label: 'Mot de passe (saisie masquée)',
            required: true,
            validate: fn (string $v) => strlen($v) < 12 ? 'Au moins 12 caractères.' : null,
        );

        $confirmation = password(label: 'Confirmez le mot de passe', required: true);

        if (!hash_equals($plain, $confirmation)) {
            $this->error('Les deux mots de passe ne correspondent pas. Rien n\'a été modifié.');
            return self::FAILURE;
        }

        $admin = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $plain, 'email_verified_at' => now()] // cast 'hashed' => bcrypt automatique
        );
        $admin->syncRoles(['admin']);
        unset($plain, $confirmation);

        $this->info("Administrateur prêt : {$admin->email} (id {$admin->id})");

        // --- Suppression des anciens comptes de test
        $old = User::whereIn('email', self::OLD_TEST_ACCOUNTS)->get();
        if ($old->isEmpty()) {
            $this->line('Aucun ancien compte de test trouvé.');
        } elseif (confirm(
            'Supprimer les anciens comptes de test (' . $old->pluck('email')->implode(', ') . ') ? Leurs tickets et commentaires seront transférés au nouvel admin.',
            default: true,
        )) {
            DB::transaction(function () use ($old, $admin) {
                foreach ($old as $u) {
                    $u->tickets()->update(['user_id' => $admin->id]);
                    $u->commentaires()->update(['user_id' => $admin->id]);
                    $u->tokens()->delete();
                    $u->delete();
                    $this->line("Supprimé : {$u->email}");
                }
            });
        }

        // --- Contrôle final : qui est admin ?
        $this->newLine();
        $this->info('Administrateurs actuels (vérifiez qu\'il n\'y a que des comptes connus) :');
        table(
            ['id', 'email', 'créé le'],
            User::role('admin')->get()->map(fn ($u) => [$u->id, $u->email, (string) $u->created_at])->all()
        );

        return self::SUCCESS;
    }
}
