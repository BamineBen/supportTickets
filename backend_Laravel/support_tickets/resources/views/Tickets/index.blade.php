<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mes Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Créer un ticket') }}
                </a>
            </div>

            @foreach($tickets as $ticket)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">{{ $ticket->titre }}</h3>
                        <p class="mb-2 text-gray-700">{{ Str::limit($ticket->description, 150) }}</p>
                        <p class="mb-3">Statut : 
                            <span class="inline-flex items-center rounded-md bg-{{ $ticket->statut === 'ouvert' ? 'green' : ($ticket->statut === 'en cours' ? 'yellow' : 'gray') }}-100 px-2 py-1 text-xs font-medium text-{{ $ticket->statut === 'ouvert' ? 'green' : ($ticket->statut === 'en cours' ? 'yellow' : 'gray') }}-700">
                                {{ ucfirst($ticket->statut) }}
                            </span>
                        </p>
                        <div class="flex space-x-2">
                            <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Voir') }}
                            </a>
                            @if(Auth::id() === $ticket->user_id || Auth::user()->hasRole('admin'))
                                <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center px-3 py-1 bg-amber-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Modifier') }}
                                </a>
                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')">
                                        {{ __('Supprimer') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
