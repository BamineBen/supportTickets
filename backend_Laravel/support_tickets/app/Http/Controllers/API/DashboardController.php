<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API pour le tableau de bord et les statistiques"
 * )
 */

class DashboardController extends Controller
{
    /**
     * Remarque : Nous n'utilisons pas de middleware dans le constructeur du contrôleur.
     * L'authentification est déjà gérée dans les routes via le groupe middleware 'auth:sanctum'
     * défini dans routes/api.php
     */
    public function __construct()
    {
        // L'authentification est gérée au niveau des routes
    }

    /**
     * Récupère les statistiques de base pour le tableau de bord utilisateur
     *
     * @OA\Get(
     *     path="/api/dashboard/user-stats",
     *     summary="Statistiques pour le tableau de bord utilisateur",
     *     description="Récupère les statistiques relatives aux tickets de l'utilisateur connecté",
     *     operationId="dashboardUserStats",
     *     tags={"Dashboard"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="tickets_par_statut", type="object"),
     *                 @OA\Property(property="tickets_en_retard", type="integer", example=2),
     *                 @OA\Property(property="total_tickets", type="integer", example=10),
     *                 @OA\Property(property="dernier_ticket", type="object"),
     *                 @OA\Property(property="total_commentaires", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userStats()
    {
        try {
            $user = Auth::user();

            // Tickets de l'utilisateur groupés par statut
            $ticketsByStatus = Ticket::where('user_id', $user->id)
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get()
                ->pluck('total', 'statut')
                ->toArray();

            // S'assurer que tous les statuts sont présents dans l'array
            $statuts = ['ouvert', 'en cours', 'fermé'];
            foreach ($statuts as $statut) {
                if (!isset($ticketsByStatus[$statut])) {
                    $ticketsByStatus[$statut] = 0;
                }
            }

            // Tickets qui ont une deadline et sont en retard
            $ticketsEnRetard = Ticket::where('user_id', $user->id)
                ->where('deadline', '<', Carbon::today())
                ->where('statut', '!=', 'fermé')
                ->count();

            // Dernier ticket créé
            $dernierTicket = Ticket::where('user_id', $user->id)
                ->latest()
                ->first();

            // Nombre total de commentaires laissés par l'utilisateur
            $totalCommentaires = Commentaire::where('user_id', $user->id)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'tickets_par_statut' => $ticketsByStatus,
                    'tickets_en_retard' => $ticketsEnRetard,
                    'total_tickets' => array_sum($ticketsByStatus),
                    'dernier_ticket' => $dernierTicket,
                    'total_commentaires' => $totalCommentaires
                ]
            ]);
            
        } catch (\Throwable $e) {
            // Log l'erreur pour le débogage
            \Log::error('Erreur dans userStats: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retourner une réponse d'erreur structurée
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des statistiques.',
            ], 500);
        }
    }

    /**
     * Récupère les statistiques complètes pour le tableau de bord administrateur
     *
     * @OA\Get(
     *     path="/api/dashboard/admin-stats",
     *     summary="Statistiques avancées pour le tableau de bord admin",
     *     description="Récupère des statistiques détaillées sur tous les tickets et utilisateurs (admin uniquement)",
     *     operationId="dashboardAdminStats",
     *     tags={"Dashboard"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="tickets_par_statut", type="object"),
     *                 @OA\Property(property="tickets_en_retard", type="integer", example=5),
     *                 @OA\Property(property="tickets_par_mois", type="object"),
     *                 @OA\Property(property="top_utilisateurs", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="stats_generales", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminStats()
    {
        // Vérifier si l'utilisateur a la permission de voir les stats avancées
        if (!Auth::user()->hasPermissionTo('voir_stats_avancees')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Tickets groupés par statut
        $ticketsByStatus = Ticket::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get()
            ->pluck('total', 'statut')
            ->toArray();

        // S'assurer que tous les statuts sont présents dans l'array
        $statuts = ['ouvert', 'en cours', 'fermé'];
        foreach ($statuts as $statut) {
            if (!isset($ticketsByStatus[$statut])) {
                $ticketsByStatus[$statut] = 0;
            }
        }

        // Tickets en retard
        $ticketsEnRetard = Ticket::where('deadline', '<', Carbon::today())
            ->where('statut', '!=', 'fermé')
            ->count();

        // Tickets créés par mois (6 derniers mois)
        $sixMoisDerniers = Carbon::now()->subMonths(5)->startOfMonth();
        $ticketsParMois = Ticket::where('created_at', '>=', $sixMoisDerniers)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'mois' => $date->format('M Y'),
                    'total' => $item->total
                ];
            })
            ->pluck('total', 'mois')
            ->toArray();

        // Top 5 utilisateurs avec le plus de tickets
        $topUtilisateurs = User::withCount('tickets')
            ->orderBy('tickets_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_tickets' => $user->tickets_count
                ];
            });

        // Statistiques générales
        $statsGenerales = [
            'total_tickets' => Ticket::count(),
            'total_utilisateurs' => User::count(),
            'total_commentaires' => Commentaire::count(),
            'tickets_sans_commentaires' => Ticket::doesntHave('commentaires')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'tickets_par_statut' => $ticketsByStatus,
                'tickets_en_retard' => $ticketsEnRetard,
                'tickets_par_mois' => $ticketsParMois,
                'top_utilisateurs' => $topUtilisateurs,
                'stats_generales' => $statsGenerales
            ]
        ]);
    }

    /**
     * Récupère les tickets récents pour le tableau de bord
     *
     * @OA\Get(
     *     path="/api/dashboard/recent-tickets",
     *     summary="Tickets récents",
     *     description="Récupère les tickets les plus récemment créés",
     *     operationId="dashboardRecentTickets",
     *     tags={"Dashboard"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre maximum de tickets à récupérer",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tickets récents récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
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
    public function recentTickets(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 5);

        if ($user->hasRole('admin')) {
            // Les admins voient tous les tickets récents
            $tickets = Ticket::with('user')
                ->latest()
                ->take($limit)
                ->get();
        } else {
            // Les utilisateurs ne voient que leurs propres tickets
            $tickets = Ticket::where('user_id', $user->id)
                ->latest()
                ->take($limit)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Récupère les tickets avec deadline proche pour le tableau de bord
     * 
     * @OA\Get(
     *     path="/api/dashboard/upcoming-deadlines",
     *     summary="Tickets avec deadlines à venir",
     *     description="Récupère les tickets dont la deadline approche",
     *     operationId="dashboardUpcomingDeadlines",
     *     tags={"Dashboard"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Tickets avec deadlines à venir récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upcomingDeadlines()
    {
        $user = Auth::user();

        // Date d'aujourd'hui et date dans 7 jours
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);

        if ($user->hasRole('admin')) {
            // Les admins voient tous les tickets avec deadline proche
            $tickets = Ticket::with('user')
                ->whereBetween('deadline', [$today, $nextWeek])
                ->where('statut', '!=', 'fermé')
                ->orderBy('deadline')
                ->take(5)
                ->get();
        } else {
            // Les utilisateurs ne voient que leurs propres tickets
            $tickets = Ticket::where('user_id', $user->id)
                ->whereBetween('deadline', [$today, $nextWeek])
                ->where('statut', '!=', 'fermé')
                ->orderBy('deadline')
                ->take(5)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }
}
