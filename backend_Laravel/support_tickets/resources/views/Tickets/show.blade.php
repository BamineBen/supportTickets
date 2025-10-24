<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Détails du Ticket') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Retour au dashboard') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold mb-2">{{ $ticket->titre }}</h1>
                            <p class="text-sm text-gray-600 mb-2">Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}</p>
                            @if($ticket->deadline)
                                <p class="text-sm text-gray-600 mb-2">
                                    <span class="font-medium">Date limite :</span> 
                                    <span class="{{ $ticket->deadline->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                        {{ $ticket->deadline->format('d/m/Y') }}
                                        @if($ticket->deadline->isPast())
                                            (Expiré)
                                        @elseif($ticket->deadline->isToday())
                                            (Aujourd'hui)
                                        @else
                                            (Dans {{ now()->diffInDays($ticket->deadline) }} jours)
                                        @endif
                                    </span>
                                </p>
                            @endif
                            <span class="inline-flex items-center rounded-md bg-{{ $ticket->statut === 'ouvert' ? 'green' : ($ticket->statut === 'en cours' ? 'yellow' : 'gray') }}-100 px-2 py-1 text-xs font-medium text-{{ $ticket->statut === 'ouvert' ? 'green' : ($ticket->statut === 'en cours' ? 'yellow' : 'gray') }}-700">
                                {{ ucfirst($ticket->statut) }}
                            </span>
                        </div>
                        
                        @if(Auth::id() === $ticket->user_id || Auth::user()->hasRole('admin'))
                            <div class="space-x-2">
                                <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center px-3 py-1 bg-amber-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Éditer') }}
                                </a>
                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')">
                                        {{ __('Supprimer') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-gray-700 whitespace-pre-line">{{ $ticket->description }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Commentaires</h3>
                    
                    @if($ticket->commentaires->isEmpty())
                        <p class="text-gray-500 italic">Aucun commentaire pour l'instant.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($ticket->commentaires as $commentaire)
                                <div class="p-4 border rounded-lg bg-gray-50">
                                    <div class="flex items-center mb-2">
                                        <span class="font-medium">{{ $commentaire->user->name }}</span>
                                        <span class="text-xs text-gray-500 ml-2">{{ $commentaire->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-gray-700">{{ $commentaire->contenu }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6">
                        <h4 class="text-md font-semibold mb-2">Ajouter un commentaire</h4>
                        <form action="{{ route('commentaires.store', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="contenu" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Envoyer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
