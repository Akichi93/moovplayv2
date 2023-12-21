<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Front\IndexController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Front\ClientController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Front\ServicesController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\PartenaireController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Front\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/bundle', [ServicesController::class, 'bundle']);


// Users Register Form Submit
Route::post('/clientregister', [ClientController::class, 'register'])->name('otp.generate');
Route::post('/clientforgot', [ClientController::class, 'forgot'])->name('otp.forgot');
Route::post('/uploadfile', [ClientController::class, 'uploadfile'])->name('upload.file');

Route::get('/compte', [ClientController::class, 'compte']);
Route::get('/profil', [ClientController::class, 'profil']);
Route::get('/changepassword', [ClientController::class, 'changepassword']);
Route::post('/logoutclient', [ClientController::class, 'logoutClient']);
Route::post('/updateinfo', [ClientController::class, 'updateinfo']);
Route::post('/updatepassword', [ClientController::class, 'updatepassword']);
Route::get('/verification/{id}', [ClientController::class, 'verification'])->name('verification');
Route::get('/verificationreset/{id}', [ClientController::class, 'verificationreset'])->name('verificationreset');
Route::post('/otplogin', [ClientController::class, 'loginWithOtp'])->name('otp.getlogin');
Route::post('/otpreset', [ClientController::class, 'resetWithOtp'])->name('otp.getreset');

Route::post('/desabonnement', [ClientController::class, 'desabonnement'])->name('desabonnement');

Route::post('/otpdemande', [ServicesController::class, 'demandeWithOtp'])->name('otp.getdemande');

Route::post('/demandeservice', [OrderController::class, 'demandeService'])->name('demande.service');
Route::get('/demandeotp/{order_url}', [OrderController::class, 'demandeotp'])->name('demandeotp');

Route::post('/resendotp', [ClientController::class, 'resendOtp'])->name('otp.resendotp');


// Users Login/Register Page
Route::get('/inscription', [ClientController::class, 'userLoginRegister']);
Route::get('/resetpassword', [ClientController::class, 'resetPassword']);
Route::post('/loginclient', [ClientController::class, 'loginclient']);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['admin']], function () {

    // Catégorie
    Route::get('/categories', [CategorieController::class, 'categories']);
    Route::match(['get', 'post'], 'add-edit-categorie/{id?}', [CategorieController::class, 'addEditCategorie']);

    // Catégorie
    Route::get('/admins', [AdminController::class, 'admins']);
    Route::match(['get', 'post'], 'add-edit-admin/{id?}', [AdminController::class, 'addEditAdmin']);

    // Partenaire
    Route::get('/partenaires', [PartenaireController::class, 'partenaires']);
    Route::match(['get', 'post'], 'add-edit-partenaire/{id?}', [PartenaireController::class, 'addEditPartenaire']);
    Route::post('partenaires/desactivate/{id}', [PartenaireController::class, 'desactivatepartenaire'])->name('desactivate.partenaires');
    Route::post('partenaires/activate/{id}', [PartenaireController::class, 'activatepartenaire'])->name('activate.partenaires');

    // Service
    Route::get('/services', [ServiceController::class, 'services']);
    Route::match(['get', 'post'], 'add-edit-service/{id?}', [ServiceController::class, 'addEditService']);
    Route::post('services/desactivate/{id}', [ServiceController::class, 'desactivateservice'])->name('desactivate.services');
    Route::post('services/activate/{id}', [ServiceController::class, 'activateservice'])->name('activate.services');
    Route::match(['get', 'post'], 'add-offres/{id}', [ServiceController::class, 'addOffres']);
    Route::post('edit-offres/{id?}', [ServiceController::class, 'editOffres']);

    // Banner
    Route::get('/slides', [BannerController::class, 'slides']);
    Route::match(['get', 'post'], 'add-edit-slide/{id?}', [BannerController::class, 'addEditSlide']);
});

// Index
Route::get('/', [IndexController::class, 'pages']);
Route::post('/search', [IndexController::class, 'search']);
Route::get('/{url}', [ServicesController::class, 'listing'])->name('listing');
Route::get('/detailservice/{service_url}', [ServicesController::class, 'details']);


Route::post('/postlogin', [AdminController::class, 'postLogin']);
