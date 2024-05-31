<?php

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Front\IndexController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Front\ClientController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Front\ServicesController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\ChaineController;
use App\Http\Controllers\Admin\PartenaireController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReportController;

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


URL::forceRootUrl('https://demo.moovplay.com/');
// Route::get('/bundle', [ServicesController::class, 'bundle']);

Route::get('/', function () {
    return view('welcome');
});

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
    Route::get('edit-images/{id?}', [ServiceController::class, 'editImages']);
    Route::post('update-images', [ServiceController::class, 'updateImages']);

    // Banner
    Route::get('/slides', [BannerController::class, 'slides']);
    Route::match(['get', 'post'], 'add-edit-slide/{id?}', [BannerController::class, 'addEditSlide']);

    // Faq
    Route::get('/questions', [QuestionController::class, 'questions']);
    Route::match(['get', 'post'], 'add-edit-question/{id?}', [QuestionController::class, 'addEditQuestion']);

     // Report
    Route::get('/reports', [ReportController::class, 'Reports']);

    // Chaine
    Route::get('/chaines', [ChaineController::class, 'chaines']);
    Route::match(['get', 'post'], 'add-edit-chaine/{id?}', [ChaineController::class, 'addEditChaine']);
});

// Index
Route::get('/', [IndexController::class, 'pages']);
Route::post('/search', [IndexController::class, 'search']);
Route::get('/{url}', [ServicesController::class, 'listing'])->name('listing');
Route::get('/detailservice/{service_url}', [ServicesController::class, 'details']);


Route::post('/postlogin', [AdminController::class, 'postLogin']);

Route::view('/{path}','errors.404');
