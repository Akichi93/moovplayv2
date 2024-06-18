<?php

namespace App\Http\Controllers\Front;

use App\Models\User;
use App\Models\Offre;
use App\Models\Abonne;
use App\Models\Banner;
use App\Models\Chaine;
use App\Models\Favori;
use App\Models\Service;
use App\Models\Categorie;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
   public function pages(Request $request)
   {
      $request->session()->put('page', '/');

      $services = Categorie::with('service')
         ->orderby('position', 'asc')
         ->get()
         ->map(function ($query) {
            $query->setRelation('service', $query->service->take(4));
            return $query;
         });


      $slides = Banner::all();

      $userIsAuthenticated = auth()->check();

      if ($userIsAuthenticated == null) {
         $imagecompte = [];
      } else {
         $imagecompte = auth()->user()->image;
      }

      return view('web.welcome')->with(compact('services', 'userIsAuthenticated', 'imagecompte', 'slides'));
   }

   public function search(Request $request)
   {
      $q = $request->search;
      $result = Service::where('nom_service', 'LIKE', '%' . $q . '%')->get();
      return view('web.pages')->with(compact('result', 'q'));
   }

   public function getCategories()
   {
      try {
         $categories = Categorie::all();

         return response()->json([
            'success' => true,
            'data' => $categories
         ], Response::HTTP_OK);
      } catch (\Exception $exception) {
         Log::info('erreur :', ['data' => $exception]);
         return response()->json([
            'success' => false,
            'message' => 'Veuillez ressayez plus tard.',
         ], Response::HTTP_OK);
      }
   }

   public function getServices(Request $request)
   {
      try {
         $services = null;

         if ($services === null) {
            $services = Service::where('status', 0)->get();
         }

         if ($request->has('category')) {
            // $services = Categorie::where('url', $request->category)->with('service')->get();

            $services = Categorie::where('url', $request->category)
               ->with(['service' => function ($query) {
                  $query->where('status', 0);
               }])
               ->get();
         }

         if ($request->has('slug')) {
            $services = Service::with('categories')
               ->where('service_url', $request->slug)
               ->where('status', 0)
               ->first();
         }

         if ($request->has('limit')) {
            $limit = $request->limit;
            $services = Categorie::where('url', $request->category)->with('service')
               ->get()
               ->map(function ($query) use ($limit) {
                  $query->setRelation('service', $query->service->take($limit));
                  return $query;
               });
         }

         if ($request->has('sort')) {
            if ($request->sort == 'random') {
               $services = Service::inRandomOrder()->where('status', 0)->get();
            }

            if ($request->sort == 'newer') {
               $services = Service::orderby('id', 'asc')->where('status', 0)->get();
            }
         }

         if ($request->has('search')) {
            $pattern = $request->search;

            $characters = str_split($pattern);

            // Initialise le motif d'expression régulière
            $pattern = '';

            // Parcourt chaque caractère et les concatène avec '.*'
            foreach ($characters as $char) {
               $pattern .= $char . '.*';
            }

            $pattern = rtrim($pattern, '.*');

            // Utilisation de l'opérateur REGEXP avec une expression régulière pour correspondre aux occurrences de 'q' et 'm' dans la même chaîne
            // $services = Service::whereRaw("CONCAT(description, ' ', nom_service) REGEXP ?", [$pattern])
            //    ->get();

            $services = Service::whereRaw("nom_service REGEXP ?", [$pattern])
               ->where('status', 0)
               ->get();
         }

         return response()->json([
            'success' => true,
            'data' => $services
         ], Response::HTTP_OK);
      } catch (\Exception $exception) {
         Log::info('erreur :', ['data' => $exception]);
         return response()->json([
            'success' => false,
            'message' => 'Veuillez ressayez plus tard.',
         ], Response::HTTP_OK);
      }
   }

   public function getBanners()
   {
      try {
         $banners = Banner::all();

         return response()->json([
            'success' => true,
            'data' => $banners
         ], Response::HTTP_OK);
      } catch (\Exception $exception) {
         Log::info('erreur :', ['data' => $exception]);
         return response()->json([
            'success' => false,
            'message' => 'Veuillez ressayez plus tard.',
         ], Response::HTTP_OK);
      }
   }

   public function details($service_url)
   {

      $productDetails = Service::with('categories')->where('service_url', $service_url)->first();
      if ($productDetails == null) {

         return response()->json([
            'success' => false,
            'data' => 'Ce service n\'existe pas'
         ], Response::HTTP_OK);

         //   abort(404);
      } else {
         $Data['productDetails'] = Service::with('categories')->where('service_url', $service_url)->first();

         $Data['relatedProducts'] = Service::limit(4)->inRandomOrder()->where('service_url', '!=', $service_url)->where('status', 0)->get();

         $categorie_id = Service::with('categories')->where('service_url', $service_url)->value('categorie_id');

         $Data['otherservices'] = Service::with('categories')->inRandomOrder()
            ->where('categorie_id', $categorie_id)
            ->where('service_url', '!=', $service_url)
            ->where('status', 0)
            ->limit(4)
            ->get();

         return response()->json([
            'success' => true,
            'data' => $Data
         ], Response::HTTP_OK);
      }
   }

   public function detailsCategory($category_url)
   {
      $Data = Categorie::where('url', $category_url)->get();

      return response()->json([
         'success' => true,
         'data' => $Data
      ], Response::HTTP_OK);
   }

   public function getChannels()
   {
      $Data = Chaine::all();

      return response()->json([
         'success' => true,
         'data' => $Data
      ], Response::HTTP_OK);
   }

   public function updateSubscription(Request $request)
   {
      $data = $request->all();

      foreach ($data as $subscriptionData) {

         // Extraire le contact en supprimant les 3 premiers caractères
         $contact = substr($subscriptionData['msisdn'], 3);

         // Rechercher le service correspondant
         $service = Service::where('service_name', $subscriptionData['service_name'])->first();

         // Rechercher l'utilisateur correspondant
         $user = User::where('contact', $contact)->first();

         // Vérifier si l'utilisateur existe
         if (!$user) {
            // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
            $user = User::create([
               'contact' => '225' . $contact,
               'status' => 0,
            ]);
         }

         // Mettre à jour ou créer la transaction
         Transaction::updateOrCreate(
            ['msisdn' => $subscriptionData['msisdn']],
            [
               'nom_service' => $service['nom_service'],
               'forfait' => $subscriptionData['forfait'],
               'amount' => $subscriptionData['amount'],
               'transaction_id' => $subscriptionData['transaction_id'],
               'service_name' => $subscriptionData['service_name'],
               'date_fin_abonnement' => $subscriptionData['date_fin_abonnement'],
               'user_id' => $user->id,
               'service_id' => $service->id,
               'partenaire_id' => $service->partenaire_id,
            ]
         );
      }
   }
}
