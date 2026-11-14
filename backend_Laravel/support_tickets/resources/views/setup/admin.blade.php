@extends('setup.layout')
@section('content')
    <h1>Créer l'administrateur</h1>
    <p class="sub">Page temporaire, utilisable une seule fois.</p>

    <form method="POST" action="{{ url('/setup-admin') }}" autocomplete="off">
        @csrf

        <label for="setup_token">Code de configuration</label>
        <input id="setup_token" name="setup_token" type="password" required autocomplete="off">
        <div class="hint">La valeur de ADMIN_SETUP_TOKEN dans votre fichier .env</div>
        @error('setup_token')<div class="err">{{ $message }}</div>@enderror

        <hr>

        <label for="name">Nom affiché</label>
        <input id="name" name="name" type="text" value="{{ old('name', 'Administrateur') }}" required>
        @error('name')<div class="err">{{ $message }}</div>@enderror

        <label for="email">Adresse email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="off">
        @error('email')<div class="err">{{ $message }}</div>@enderror

        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
        <div class="hint">12 caractères minimum</div>
        @error('password')<div class="err">{{ $message }}</div>@enderror

        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">

        @if ($oldAccounts->isNotEmpty())
            <label class="check">
                <input type="checkbox" name="delete_old" value="1" checked>
                <span>Supprimer les anciens comptes de test ({{ $oldAccounts->implode(', ') }}) et transférer leurs tickets au nouvel admin</span>
            </label>
        @endif

        <button type="submit">Créer l'administrateur</button>
    </form>
@endsection
