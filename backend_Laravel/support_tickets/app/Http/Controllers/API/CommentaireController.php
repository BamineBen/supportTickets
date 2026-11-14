<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commentaire;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\CommentAddedNotification;

/**
 * @OA\Tag(
 *     name="Commentaires",
 *     description="API pour la gestion des commentaires sur les tickets"
 * )
 */

class CommentaireController extends Controller
{
    /**
     * Affiche tous les commentaires d'un ticket spécifique
     * GET /api/tickets/{ticket_id}/commentaires
     * 
     * @OA\Get(
     *     path="/api/tickets/{ticket_id}/commentaires",
     *     summary="Liste tous les commentaires d'un ticket",
     *     description="Retourne une liste de tous les commentaires associés à un ticket spécifique",
     *     operationId="listTicketComments",
     *     tags={"Commentaires"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="path",
     *         description="ID du ticket",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des commentaires récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Non autorisé à voir les commentaires de ce ticket")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket non trouvé"
     *     )
     * )
     * 
     * @param int $ticket_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);
        
        // Vérifier si l'utilisateur est autorisé à voir ce ticket
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $ticket->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé à voir les commentaires de ce ticket'
            ], 403);
        }
        
        $commentaires = Commentaire::where('ticket_id', $ticket_id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $commentaires
        ]);
    }

    /**
     * Ajoute un nouveau commentaire à un ticket
     * POST /api/tickets/{ticket_id}/commentaires
     * 
     * @OA\Post(
     *     path="/api/tickets/{ticket_id}/commentaires",
     *     summary="Ajoute un nouveau commentaire",
     *     description="Ajoute un nouveau commentaire à un ticket spécifique",
     *     operationId="createTicketComment",
     *     tags={"Commentaires"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="path",
     *         description="ID du ticket",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contenu"},
     *             @OA\Property(property="contenu", type="string", example="Merci pour votre demande, je vais m'en occuper.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Commentaire ajouté avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Commentaire ajouté avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ticket fermé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Impossible d'ajouter un commentaire à un ticket fermé")
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
     * @param int $ticket_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);

        // Seuls le propriétaire du ticket et les admins peuvent commenter
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $ticket->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé à commenter ce ticket'
            ], 403);
        }
        
        // Vérifier si le ticket n'est pas fermé
        if ($ticket->statut === 'fermé') {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible d\'ajouter un commentaire à un ticket fermé'
            ], 400);
        }
        
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'contenu' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Créer le commentaire
        $commentaire = Commentaire::create([
            'contenu' => $request->contenu,
            'user_id' => Auth::id(),
            'ticket_id' => $ticket_id,
        ]);
        
        // Charger les relations pour la réponse
        $commentaire->load('user');
        
        // Envoyer une notification au créateur du ticket s'il n'est pas l'auteur du commentaire
        if ($ticket->user_id !== Auth::id()) {
            $ticket->user->notify(new CommentAddedNotification($commentaire, $ticket));
        }
        
        // Envoyer une notification aux administrateurs (sauf s'ils sont l'auteur du commentaire)
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id !== Auth::id() && $admin->id !== $ticket->user_id) {
                $admin->notify(new CommentAddedNotification($commentaire, $ticket));
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Commentaire ajouté avec succès',
            'data' => $commentaire
        ], 201);
    }

    /**
     * Affiche un commentaire spécifique
     * GET /api/commentaires/{id}
     * 
     * @OA\Get(
     *     path="/api/commentaires/{id}",
     *     summary="Détails d'un commentaire",
     *     description="Affiche les détails d'un commentaire spécifique",
     *     operationId="showComment",
     *     tags={"Commentaires"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du commentaire",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du commentaire récupérés avec succès",
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
     *             @OA\Property(property="message", type="string", example="Non autorisé à voir ce commentaire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commentaire non trouvé"
     *     )
     * )
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $commentaire = Commentaire::with(['user', 'ticket'])->findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à voir ce commentaire
        $ticket = $commentaire->ticket;
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $ticket->user_id && Auth::id() !== $commentaire->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé à voir ce commentaire'
            ], 403);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $commentaire
        ]);
    }

    /**
     * Met à jour un commentaire existant
     * PUT /api/commentaires/{id}
     * 
     * @OA\Put(
     *     path="/api/commentaires/{id}",
     *     summary="Met à jour un commentaire",
     *     description="Modifie le contenu d'un commentaire existant",
     *     operationId="updateComment",
     *     tags={"Commentaires"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du commentaire",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contenu"},
     *             @OA\Property(property="contenu", type="string", example="Contenu mis à jour du commentaire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commentaire mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Commentaire mis à jour avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ticket fermé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Impossible de modifier un commentaire sur un ticket fermé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Non autorisé à modifier ce commentaire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commentaire non trouvé"
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
        $commentaire = Commentaire::findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à modifier ce commentaire
        // Seul l'auteur du commentaire ou un admin peut le modifier
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $commentaire->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé à modifier ce commentaire'
            ], 403);
        }
        
        // Vérifier si le ticket n'est pas fermé
        $ticket = $commentaire->ticket;
        if ($ticket->statut === 'fermé') {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de modifier un commentaire sur un ticket fermé'
            ], 400);
        }
        
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'contenu' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Mettre à jour le commentaire
        $commentaire->update([
            'contenu' => $request->contenu,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Commentaire mis à jour avec succès',
            'data' => $commentaire
        ]);
    }

    /**
     * Supprime un commentaire
     * DELETE /api/commentaires/{id}
     * 
     * @OA\Delete(
     *     path="/api/commentaires/{id}",
     *     summary="Supprime un commentaire",
     *     description="Supprime un commentaire existant",
     *     operationId="deleteComment",
     *     tags={"Commentaires"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du commentaire",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commentaire supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Commentaire supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ticket fermé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Impossible de supprimer un commentaire sur un ticket fermé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Non autorisé à supprimer ce commentaire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commentaire non trouvé"
     *     )
     * )
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $commentaire = Commentaire::findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à supprimer ce commentaire
        // Seul l'auteur du commentaire, le propriétaire du ticket ou un admin peut le supprimer
        $ticket = $commentaire->ticket;
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $commentaire->user_id && Auth::id() !== $ticket->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé à supprimer ce commentaire'
            ], 403);
        }
        
        // Vérifier si le ticket n'est pas fermé
        if ($ticket->statut === 'fermé' && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer un commentaire sur un ticket fermé'
            ], 400);
        }
        
        $commentaire->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Commentaire supprimé avec succès'
        ]);
    }
}
