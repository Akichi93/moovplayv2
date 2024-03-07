<?php

use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Front\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\IndexController;
use App\Http\Controllers\Front\ServicesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

    Route::get('/profil', [AuthController::class, 'userProfile']);
    Route::get('/compte', [AuthController::class, 'compte']);
    Route::post('/desabonnement', [AuthController::class, 'desabonnement']);
});

Route::get('/getcategories', [IndexController::class, 'getCategories']);
Route::get('/getservices', [IndexController::class, 'getServices']);
Route::get('/detailservice/{service_url}', [IndexController::class, 'details']);
Route::get('/getbanners', [IndexController::class, 'getBanners']);
Route::post('/demandeservice', [ServicesController::class, 'demandeService']);
Route::post('/confirmationdemande', [ServicesController::class, 'demandeWithOtp']);
