<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PromotionController;
use App\Http\Middleware\FirebaseAuthMiddleware;
use App\Services\FirebaseUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\FirebaseService;
use App\Http\Controllers\TestFirebaseController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\ReferentielController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::get('/test-firebase', [TestFirebaseController::class, 'test']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/registerAdmin', [AuthController::class, 'register']);
/* Route::post(uri: '/upload', [FileController::class, 'uploadFile']);
 */


/* }); */

Route::post('/login/google', function (Request $request, FirebaseUserService $firebaseUserService) {
    $token = $request->input('token');
    return $firebaseUserService->authenticateWithGoogleToken($token);
});



Route::post('/users', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('veri', [AuthController::class, 'verify']);

// routes/api.php
Route::middleware([FirebaseAuthMiddleware::class])->group(function () {
 

Route::post('/users/store', [AuthController::class, 'store']);
Route::get('/users/lister', [AuthController::class, 'index']);
Route::put('/users/{firebaseUid}', [AuthController::class, 'update']);
Route::get('/users/filter/{roleId}', [AuthController::class, 'filterByRole']);
Route::get('/export-users', [AuthController::class, 'exportUsers']);
Route::get('/export-users-pdf', [AuthController::class, 'exportUsersPDF']);



// Route pour créer un nouveau référentiel
Route::post('/referentiels', [ReferentielController::class, 'create']);

// Route pour lister tous les référentiels
Route::get('/referentiels', [ReferentielController::class, 'index']);

// Route pour récupérer un référentiel par ID
Route::get('/referentiels/{id}', [ReferentielController::class, 'show']);

// Route pour mettre à jour un référentiel
Route::patch('/referentiels/{id}', [ReferentielController::class, 'update']);

// Route pour supprimer un référentiel (soft delete)
Route::delete('/referentiels/{id}', [ReferentielController::class, 'destroy']);

// Route pour lister les référentiels archivés
Route::get('/archive/referentiels', [ReferentielController::class, 'listArchived']);
/* }); */

//promotion
// Route pour créer une promotion


Route::prefix('/promotions')->group(function () {
    // Créer une nouvelle promotion
    Route::post('/', [PromotionController::class, 'create']);

    // Lister toutes les promotions
    Route::get('/', [PromotionController::class, 'index']);

    // Récupérer une promotion par ID
    Route::get('/{id}', [PromotionController::class, 'show']);

    // Mettre à jour une promotion
    Route::patch('/{id}', [PromotionController::class, 'update']);

    // Ajouter ou retirer un référentiel actif d'une promotion
    Route::patch('/{id}/referentiels', [PromotionController::class, 'patchReferentiel']);

    // Changer le statut d'une promotion
    Route::patch('/{id}/etat', [PromotionController::class, 'updateEtat']);

    // Récupérer la promotion en cours
    Route::get('/encours', [PromotionController::class, 'getPromotionEncours']);

    // Clôturer une promotion
    Route::patch('/{id}/cloturer', [PromotionController::class, 'cloturerPromotion']);

    // Lister les référentiels actifs d'une promotion
    Route::get('/{id}/referentiels', [PromotionController::class, 'getReferentiels']);

    // Récupérer les statistiques d'une promotion
    Route::get('/{id}/stats', [PromotionController::class, 'getPromotionStats']);
});
});
