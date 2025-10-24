<?php

namespace App\Http\Controllers\Gestion_tickets;

use App\Http\Controllers\Controller;
use App\Models\Commentaire;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentaireController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'contenu' => 'required|string',
        ]);

        Commentaire::create([
            'contenu' => $request->contenu,
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Commentaire ajouté avec succès.');
    }

}
