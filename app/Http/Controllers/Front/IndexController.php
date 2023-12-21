<?php

namespace App\Http\Controllers\Front;

use App\Models\Offre;
use App\Models\Service;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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


      $userIsAuthenticated = auth()->check();

      if ($userIsAuthenticated == null) {
         $imagecompte = [];
      } else {
         $imagecompte = auth()->user()->image;
      }

      return view('web.welcome')->with(compact('services', 'userIsAuthenticated', 'imagecompte'));
   }

   public function search(Request $request)
   {
      $q = $request->search;
      $result = Service::where('nom_service', 'LIKE', '%' . $q . '%')->get();
      return view('web.pages')->with(compact('result', 'q'));
   }
}
