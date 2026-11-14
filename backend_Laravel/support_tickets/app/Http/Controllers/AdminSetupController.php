<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * PAGE TEMPORAIRE de création d'un administrateur.
 *
 * Verrous :
 *  1. inactive (404) tant que ADMIN_SETUP_TOKEN n'est pas défini dans le .env ;
 *  2. le code saisi doit correspondre à ADMIN_SETUP_TOKEN ;
 *  3. usage unique : un fichier verrou est créé après succès, la page renvoie alors 404 ;
 *  4. route limitée à 5 requêtes par minute.
 *
 * À SUPPRIMER une fois l'administrateur créé.
 */
class AdminSetupController extends Controller
{
    private const LOCK_FILE = 'admin_setup.lock';
    private const OLD_TEST_ACCOUNTS = ['bamine@gmail.com', 'test@gmail.com'];

    private function abortIfDisabled(): void
    {
        if (!config('app.admin_setup_token') || Storage::disk('local')->exists(self::LOCK_FILE)) {
            abort(404);
        }
    }

    public function show()
    {
        $this->abortIfDisabled();

        return view('setup.admin', [
            'oldAccounts' => User::whereIn('email', self::OLD_TEST_ACCOUNTS)->pluck('email'),
        ]);
    }

    public function store(Request $request)
    {
        $this->abortIfDisabled();

        $data = $request->validate([
            'setup_token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', function ($attr, $value, $fail) {
                if (in_array(strtolower($value), self::OLD_TEST_ACCOUNTS, true)) {
                    $fail('Cette adresse est un ancien compte de test : choisissez-en une autre.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(12)],
        ], [
            'required' => 'Ce champ est obligatoire.',
            'email' => 'Adresse email invalide.',
            'password.min' => 'Le mot de passe doit contenir au moins 12 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
        ]);

        if (!hash_equals((string) config('app.admin_setup_token'), $data['setup_token'])) {
            return back()->withInput($request->only('name', 'email'))
                ->withErrors(['setup_token' => 'Code de configuration incorrect.']);
        }

        $admin = DB::transaction(function () use ($data, $request) {
            Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

            $admin = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => $data['password'], 'email_verified_at' => now()] // cast 'hashed'
            );
            $admin->syncRoles(['admin']);

            if ($request->boolean('delete_old')) {
                foreach (User::whereIn('email', self::OLD_TEST_ACCOUNTS)->get() as $old) {
                    $old->tickets()->update(['user_id' => $admin->id]);
                    $old->commentaires()->update(['user_id' => $admin->id]);
                    $old->tokens()->delete();
                    $old->delete();
                }
            }

            return $admin;
        });

        // Usage unique : la page se désactive définitivement
        Storage::disk('local')->put(self::LOCK_FILE, 'Admin créé le ' . now() . ' (id ' . $admin->id . ')');

        return view('setup.admin-done', [
            'email' => $admin->email,
            'admins' => User::role('admin')->pluck('email'),
            'frontend' => rtrim(config('app.frontend_url'), '/') . '/login',
        ]);
    }
}
