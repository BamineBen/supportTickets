@extends('setup.layout')
@section('content')
    <h1 class="ok">Administrateur créé</h1>
    <p>Le compte <strong>{{ $email }}</strong> est administrateur. Cette page est maintenant désactivée.</p>
    <p>Administrateurs actuels (vérifiez qu'il n'y a que des comptes connus) :</p>
    <ul>@foreach ($admins as $a)<li>{{ $a }}</li>@endforeach</ul>
    <p><a href="{{ $frontend }}">Se connecter sur le site</a></p>
@endsection
