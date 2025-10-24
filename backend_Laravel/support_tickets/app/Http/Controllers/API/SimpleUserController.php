<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SimpleUserController extends Controller
{
    /**
     * Liste simplifiée des utilisateurs sans vérifications complexes
     */
    public function index(Request $request)
    {
        try {
            // Retourne tous les utilisateurs sans aucune vérification
            $users = User::all();
            
            return response()->json([
                'success' => true,
                'message' => 'Liste des utilisateurs récupérée avec succès',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('SimpleUserController - Erreur: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
