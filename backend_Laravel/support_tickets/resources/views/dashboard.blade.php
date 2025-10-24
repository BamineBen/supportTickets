<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard - Mes Tickets') }}
            </h2>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 dark:bg-gray-900/80 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Créer un ticket') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @role('admin')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Accéder au panel Admin') }}
                    </a>
                </div>
            </div>
            @endrole

            @if($tickets->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="mb-4">{{ __('Vous n\'avez pas encore de tickets.') }}</p>
                        <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 dark:bg-gray-900/80 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Créer votre premier ticket') }}
                        </a>
                    </div>
                </div>
            @else
                    <form method="GET" action="{{ route('tickets.index') }}" class="mb-6">
                        <div class="flex items-center space-x-4">
                            <div>
                                <label for="sort_date" class="block text-sm font-medium text-gray-700">Trier par date</label>
                                <select name="sort_date" id="sort_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Sélectionner</option>
                                    <option value="desc" {{ request('sort_date') === 'desc' ? 'selected' : '' }}>Plus récent d’abord</option>
                                    <option value="asc" {{ request('sort_date') === 'asc' ? 'selected' : '' }}>Plus ancien d’abord</option>
                                </select>
                            </div>
                            <div>
                                <label for="sort_name" class="block text-sm font-medium text-gray-700">Trier par nom</label>
                                <select name="sort_name" id="sort_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Sélectionner</option>
                                    <option value="asc" {{ request('sort_name') === 'asc' ? 'selected' : '' }}>A-Z</option>
                                    <option value="desc" {{ request('sort_name') === 'desc' ? 'selected' : '' }}>Z-A</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="inline-flex items-center px-2 py-2 dark:bg-gray-900/80 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Trier') }}
                                </button>
                            </div>
                        </div>
                    </form>
                @foreach($tickets as $ticket)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ $ticket->titre }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}</p>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center rounded-md bg-{{ $ticket->statut === 'ouvert' ? 'green' : ($ticket->statut === 'en cours' ? 'yellow' : 'gray') }}-100 px-2 py-1 text-xs font-medium text-{{ $ticket->statut === 'ouvert' ? 'green' : ($ticket->statut === 'en cours' ? 'yellow' : 'gray') }}-700">
                                            {{ ucfirst($ticket->statut) }}
                                        </span>
                                    </div>
                                    <p class="mt-3">{{ \Illuminate\Support\Str::limit($ticket->description, 150) }}</p>
                                </div>
                                <div class="space-x-2">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center px-3 py-1 bg-blue-100 border border-transparent rounded-md font-medium text-xs text-blue-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Voir') }}
                                    </a>
                                    <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center px-3 py-1 bg-yellow-100 border border-transparent rounded-md font-medium text-xs text-yellow-700 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Éditer') }}
                                    </a>
                                    <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 border border-transparent rounded-md font-medium text-xs text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')">
                                            {{ __('Supprimer') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
            @endif
        </div>
    </div>
</x-app-layout>
