<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="API de Gestion de Tickets de Support",
 *     version="1.0.0",
 *     description="Documentation de l'API du projet de gestion de tickets de support",
 *     @OA\Contact(
 *         email="contact@example.com",
 *         name="Support API"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     description="Serveur local",
 *     url=L5_SWAGGER_CONST_HOST
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Authentification",
 *     description="Opérations d'authentification (login, register, etc.)"
 * )
 * 
 * @OA\Tag(
 *     name="Tickets",
 *     description="Opérations liées aux tickets de support"
 * )
 * 
 * @OA\Tag(
 *     name="Commentaires",
 *     description="Opérations liées aux commentaires sur les tickets"
 * )
 * 
 * @OA\Tag(
 *     name="Utilisateurs",
 *     description="Gestion des utilisateurs (admin)"
 * )
 * 
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Statistiques et tableaux de bord"
 * )
 * 
 * @OA\Tag(
 *     name="Notifications",
 *     description="Gestion des notifications"
 * )
 */
class ApiDocController extends Controller
{
    // Cette classe est utilisée uniquement pour les annotations Swagger
}
