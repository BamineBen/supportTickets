<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Utilisateurs",
 *     description="API pour la gestion des utilisateurs (admin uniquement)"
 * )
 */

class UserController extends Controller
{
    // Nous supprimons le constructeur qui cause l'erreur
    // Les middlewares sont déjà définis dans le fichier de routes
    
    /**
     * Refuse l'accès si l'utilisateur courant n'est pas administrateur.
     */
    private function denyUnlessAdmin()
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette action n\'est autorisée que pour les administrateurs'
            ], 403);
        }
        return null;
    }

    /**
     * Liste tous les utilisateurs (admin uniquement)
     *
     * @OA\Get(
     *     path="/api/users",
     *     summary="Liste tous les utilisateurs",
     *     description="Retourne une liste paginée de tous les utilisateurs avec possibilité de filtrage",
     *     operationId="listUsers",
     *     tags={"Utilisateurs"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filtrer par rôle (admin, user)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par nom ou email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès non autorisé")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        // Pagination et filtres possibles
        $query = User::query();
        
        // Filtre par rôle si spécifié
        if ($request->has('role')) {
            $role = $request->input('role');
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }
        
        // Filtre par recherche sur nom ou email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Pagination avec 10 utilisateurs par page
        $perPage = max(1, min((int) $request->input('per_page', 10), 100));
        $users = $query->with('roles')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Affiche les détails d'un utilisateur spécifique
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Affiche les détails d'un utilisateur",
     *     description="Retourne les informations détaillées d'un utilisateur spécifique",
     *     operationId="showUser",
     *     tags={"Utilisateurs"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        $user = User::with('roles')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Met à jour les informations d'un utilisateur
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Met à jour un utilisateur",
     *     description="Modifie les informations d'un utilisateur existant",
     *     operationId="updateUser",
     *     tags={"Utilisateurs"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Utilisateur mis à jour avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous n'avez pas les permissions nécessaires pour modifier les rôles")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation échouée"
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Vérification manuelle du rôle admin
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette action n\'est autorisée que pour les administrateurs'
            ], 403);
        }
        
        $user = User::findOrFail($id);
        
        // Validation des données
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|nullable|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
        ]);
        
        // Mise à jour des données de base
        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }
        
        if (isset($validatedData['email'])) {
            $user->email = $validatedData['email'];
        }
        
        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }
        
        $user->save();
        
        // Mise à jour des rôles si spécifiés
        if (isset($validatedData['roles'])) {
            // Vérifier que l'utilisateur actuel est admin
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour modifier les rôles'
                ], 403);
            }
            
            // Synchroniser les rôles
            $user->syncRoles($validatedData['roles']);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $user->load('roles')
        ]);
    }

    /**
     * Supprime un utilisateur
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Supprime un utilisateur",
     *     description="Supprime un utilisateur existant",
     *     operationId="deleteUser",
     *     tags={"Utilisateurs"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Utilisateur supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous ne pouvez pas supprimer votre propre compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Impossible de supprimer l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Impossible de supprimer cet utilisateur car il a des tickets ou commentaires associés")
     *         )
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Vérification manuelle du rôle admin
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette action n\'est autorisée que pour les administrateurs'
            ], 403);
        }
        
        // Vérifier qu'on ne supprime pas son propre compte
        if (auth()->id() == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 403);
        }
        
        $user = User::findOrFail($id);
        
        // Au lieu de bloquer la suppression, on transfère la propriété des tickets et commentaires à l'admin
        $adminId = auth()->id(); // ID de l'administrateur actuel
        
        try {
            // Démarrer une transaction pour garantir la cohérence des données
            \DB::beginTransaction();
            
            // Transférer tous les tickets de l'utilisateur à l'administrateur
            if ($user->tickets()->count() > 0) {
                $user->tickets()->update(['user_id' => $adminId]);
            }
            
            // Transférer tous les commentaires de l'utilisateur à l'administrateur
            if ($user->commentaires()->count() > 0) {
                $user->commentaires()->update(['user_id' => $adminId]);
            }
            
            // Supprimer l'utilisateur
            $user->delete();
            
            // Valider les modifications
            \DB::commit();
        } catch (\Exception $e) {
            // En cas d'erreur, annuler toutes les modifications
            \DB::rollBack();
            \Log::error('Erreur suppression utilisateur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
    
    /**
     * Affiche les utilisateurs avec le rôle spécifié
     *
     * @OA\Get(
     *     path="/api/users/role/{role}",
     *     summary="Liste les utilisateurs par rôle",
     *     description="Retourne tous les utilisateurs ayant un rôle spécifique",
     *     operationId="getUsersByRole",
     *     tags={"Utilisateurs"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="Nom du rôle (admin, user)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé"
     *     )
     * )
     *
     * @param  string  $role
     * @return \Illuminate\Http\Response
     */
    public function getUsersByRole($role)
    {
        if ($denied = $this->denyUnlessAdmin()) {
            return $denied;
        }

        $users = User::role($role)->get();
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
    
    /**
     * Change le rôle d'un utilisateur
     *
     * @OA\Patch(
     *     path="/api/users/{id}/role",
     *     summary="Change le rôle d'un utilisateur",
     *     description="Modifie le rôle d'un utilisateur existant",
     *     operationId="changeUserRole",
     *     tags={"Utilisateurs"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rôle modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rôle modifié avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le rôle spécifié n'existe pas")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeRole(Request $request, $id)
    {
        // Vérification manuelle du rôle admin
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette action n\'est autorisée que pour les administrateurs'
            ], 403);
        }
        
        $validatedData = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);
        
        $user = User::findOrFail($id);
        
        // On ne permet pas de changer son propre rôle pour éviter les problèmes
        if (auth()->id() == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier votre propre rôle'
            ], 403);
        }
        
        // Supprimer tous les rôles actuels et assigner le nouveau
        $user->syncRoles([$validatedData['role']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Rôle modifié avec succès',
            'data' => $user->load('roles')
        ]);
    }
}
