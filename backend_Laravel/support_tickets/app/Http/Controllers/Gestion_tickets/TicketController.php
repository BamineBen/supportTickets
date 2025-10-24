<?php

namespace App\Http\Controllers\Gestion_tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        // Redirection vers le dashboard qui affiche déjà les tickets
        return redirect()->route('dashboard');
    }

    public function create()
    {
        return view('Tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        Ticket::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket créé avec succès.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeView($ticket);
        return view('Tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeView($ticket);
        return view('Tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeView($ticket);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'statut' => 'in:ouvert,en cours,fermé',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        $ticket->update($request->only('titre', 'description', 'statut', 'deadline'));

        return redirect()->route('tickets.index')->with('success', 'Ticket mis à jour.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorizeView($ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket supprimé.');
    }

    private function authorizeView(Ticket $ticket)
    {
        if (Auth::user()->hasRole('admin')) return;
        if ($ticket->user_id !== Auth::id()) abort(403, 'Accès refusé');
    }
}
