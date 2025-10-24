<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier le Ticket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-4">
                            <label for="titre" class="block text-sm font-medium text-gray-700">Titre</label>
                            <input type="text" name="titre" id="titre" value="{{ $ticket->titre }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ $ticket->description }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label for="statut" class="block text-sm font-medium text-gray-700">Statut</label>
                            <select name="statut" id="statut" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="ouvert" {{ $ticket->statut === 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                                <option value="en cours" {{ $ticket->statut === 'en cours' ? 'selected' : '' }}>En cours</option>
                                <option value="fermé" {{ $ticket->statut === 'fermé' ? 'selected' : '' }}>Fermé</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="deadline" class="block text-sm font-medium text-gray-700">Date limite (optionnelle)</label>
                            <input type="date" name="deadline" id="deadline" value="{{ $ticket->deadline ? $ticket->deadline->format('Y-m-d') : '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="flex items-center justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Enregistrer les modifications') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
