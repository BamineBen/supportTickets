<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;

/**
 * @OA\Tag(
 *     name="Tickets",
 *     description="API pour la gestion des tickets de support"
 * )
 */

class TicketController extends Controller
{
    /**
     * Affiche la liste de tous les tickets
     * GET /api/tickets
     * 
     * @OA\Get(
     *     path="/api/tickets",
     *     summary="Liste tous les tickets",
     *     description="Retourne une liste paginée de tous les tickets avec possibilité de filtrage",
     *     operationId="ticketsIndex",
     *     tags={"Tickets"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut (ouvert, en cours, fermé)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ouvert", "en cours", "fermé"})
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrer par ID d'utilisateur",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Recherche textuelle dans le titre et la description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Tri par date (newest/oldest)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"newest", "oldest"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tickets récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Ticket::with('user');
        
        // Filtrer par statut si le paramètre est fourni
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Filtrer par utilisateur si le paramètre est fourni
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filtrer par recherche textuelle sur le titre ou la description
        if ($request->has('q')) {
            $query->where(function($q) use ($request) {
                $q->where('titre', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        
        // Tri par date (plus récent / plus ancien)
        if ($request->has('sort')) {
            $direction = $request->sort === 'oldest' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            // Par défaut, plus récents en premier
            $query->orderBy('created_at', 'desc');
        }
        
        // Pagination des résultats (10 par page par défaut)
        $perPage = $request->per_page ?? 10;
        $tickets = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $tickets
        ]);
    }
    
    /**
     * Affiche les tickets de l'utilisateur authentifié
     * GET /api/my-tickets
     * 
     * @OA\Get(
     *     path="/api/my-tickets",
     *     summary="Liste des tickets de l'utilisateur connecté",
     *     description="Retourne une liste paginée des tickets créés par l'utilisateur authentifié",
     *     operationId="myTickets",
     *     tags={"Tickets"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut (ouvert, en cours, fermé)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ouvert", "en cours", "fermé"})
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Tri par date (newest/oldest)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"newest", "oldest"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tickets récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTickets(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::where('user_id', $user->id);
        
        // Filtrer par statut si le paramètre est fourni
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Tri par date (plus récent / plus ancien)
        if ($request->has('sort')) {
            $direction = $request->sort === 'oldest' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            // Par défaut, plus récents en premier
            $query->orderBy('created_at', 'desc');
        }
        
        // Pagination des résultats (10 par page par défaut)
        $perPage = $request->per_page ?? 10;
        $tickets = $query->with('user')->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $tickets
        ]);
    }

    /**
     * Affiche les détails d'un ticket spécifique
     * GET /api/tickets/{id}
     * 
     * @OA\Get(
     *     path="/api/tickets/{id}",
     *     summary="Détails d'un ticket spécifique",
     *     description="Retourne les détails d'un ticket avec ses commentaires",
     *     operationId="showTicket",
     *     tags={"Tickets"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du ticket récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Non autorisé à voir ce ticket")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket non trouvé"
     *     )
     * )
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'commentaires.user'])->findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à voir ce ticket
        // Admin peut voir tous les tickets, l'utilisateur ne peut voir que ses propres tickets
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $ticket->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé à voir ce ticket'
            ], 403);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $ticket
        ]);
    }

    /**
     * Crée un nouveau ticket
     * POST /api/tickets
     * 
     * @OA\Post(
     *     path="/api/tickets",
     *     summary="Création d'un nouveau ticket",
     *     description="Crée un nouveau ticket de support",
     *     operationId="storeTicket",
     *     tags={"Tickets"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titre", "description"},
     *             @OA\Property(property="titre", type="string", maxLength=255, example="Problème de connexion"),
     *             @OA\Property(property="description", type="string", example="Je n'arrive pas à me connecter à mon compte"),
     *             @OA\Property(property="deadline", type="string", format="date", example="2025-06-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ticket créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Ticket créé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Créer le ticket
        $ticket = Ticket::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'statut' => 'ouvert', // Statut par défaut
            'user_id' => Auth::id(), // Utilisateur authentifié
            'deadline' => $request->deadline, // Date limite (optionnelle)
        ]);
        
        // Envoyer une notification aux administrateurs
        try {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                if ($admin->id !== Auth::id()) { // Ne pas s'envoyer de notification à soi-même
                    $admin->notify(new TicketCreatedNotification($ticket));
                }
            }
        } catch (\Exception $e) {
            // Log l'erreur mais continuez le traitement
            \Log::error('Erreur d\'envoi d\'email: ' . $e->getMessage());
            // On ne retourne pas d'erreur, la création du ticket continue normalement
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Ticket créé avec succès',
            'data' => $ticket
        ], 201);
    }

    /**
     * Met à jour un ticket existant
     * PUT /api/tickets/{id}
     * 
     * @OA\Put(
     *     path="/api/tickets/{id}",
     *     summary="Mise à jour d'un ticket",
     *     description="Met à jour les informations d'un ticket existant",
     *     operationId="updateTicket",
     *     tags={"Tickets"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titre", type="string", maxLength=255, example="Problème de connexion (mis à jour)"),
     *             @OA\Property(property="description", type="string", example="Je n'arrive toujours pas à me connecter à mon compte"),
     *             @OA\Property(property="statut", type="string", enum={"ouvert", "en cours", "fermé"}, example="en cours"),
     *             @OA\Property(property="deadline", type="string", format="date", example="2025-06-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Ticket mis à jour avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Non autorisé à modifier ce ticket")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Vu00e9rifier si l'utilisateur est autorisu00e9 u00e0 modifier ce ticket
        // Admin peut modifier tous les tickets, l'utilisateur ne peut modifier que ses propres tickets
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $ticket->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisu00e9 u00e0 modifier ce ticket'
            ], 403);
        }
        
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'statut' => 'sometimes|required|in:ouvert,en cours,fermé',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Seul l'admin peut changer le statut
        if ($request->has('statut') && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seul un administrateur peut changer le statut du ticket'
            ], 403);
        }
        
        // Sauvegarder l'ancien statut pour la notification
        $oldStatus = $ticket->statut;
        
        // Mettre u00e0 jour le ticket
        $ticket->update($request->only(['titre', 'description', 'statut', 'deadline']));
        
        // Envoyer une notification si le statut a changé
        if ($request->has('statut') && $oldStatus !== $request->statut) {
            // Notifier le créateur du ticket
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus));
            
            // Notifier les administrateurs (sauf si c'est l'admin qui a fait la modification)
            if (!Auth::user()->hasRole('admin')) {
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus));
                }
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Ticket mis u00e0 jour avec succu00e8s',
            'data' => $ticket
        ]);
    }

    /**
     * Supprime un ticket
     * DELETE /api/tickets/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Vu00e9rifier si l'utilisateur est autorisu00e9 u00e0 supprimer ce ticket
        // Admin peut supprimer tous les tickets, l'utilisateur ne peut supprimer que ses propres tickets
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $ticket->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisu00e9 u00e0 supprimer ce ticket'
            ], 403);
        }
        
        $ticket->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Ticket supprimu00e9 avec succu00e8s'
        ]);
    }
}
