<?php

namespace App\Http\Controllers\Front;

use Log;
use App\Models\Service;
use App\Models\Categorie;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Abonne;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ServicesController extends Controller
{
    public function listing($url, Request $request)
    {

        $search = $request['search'];
        if ($search == "") {
            $categoryCount = Categorie::where(['url' => $url, 'status' => 0])->count();
            if ($categoryCount > 0) {
                $userIsAuthenticated = auth()->check();
                if ($userIsAuthenticated == null) {
                    $imagecompte = [];
                } else {
                    $imagecompte = auth()->user()->image;
                }
                $categoryDetails = Categorie::catDetails($url);
                $categoryServices = Service::whereIn('categorie_id', $categoryDetails['catIds'])->where('status', 0);
                $title = Service::join("categories", 'services.categorie_id', '=', 'categories.id')->whereIn('categorie_id', $categoryDetails['catIds'])->first();
                $categoryServices = $categoryServices->paginate(12);
                $categories = Service::whereIn('categorie_id', $categoryDetails['catIds'])->where('status', 0)->count();



                return view('web.listing')->with(compact('categoryDetails', 'categoryServices', 'title', 'userIsAuthenticated', 'categories', 'imagecompte'));
            } else {
                abort(404);
            }
        } else {
            $categoryCount = Categorie::where(['url' => $url, 'status' => 0])->count();
            if ($categoryCount > 0) {
                $userIsAuthenticated = auth()->check();
                if ($userIsAuthenticated == null) {
                    $imagecompte = [];
                } else {
                    $imagecompte = auth()->user()->image;
                }
                $categoryDetails = Categorie::catDetails($url);
                $categoryServices = Service::whereIn('categorie_id', $categoryDetails['catIds'])
                    ->where('status', 0)->where('nom_service', 'LIKE', "%$search%");
                $title = Service::join("categories", 'services.categorie_id', '=', 'categories.id')
                    ->whereIn('categorie_id', $categoryDetails['catIds'])->first();
                $categoryServices = $categoryServices->paginate(12);
                $categories = Service::whereIn('categorie_id', $categoryDetails['catIds'])->where('status', 0)->count();



                return view('web.listing')->with(compact('categoryDetails', 'categoryServices', 'title', 'userIsAuthenticated', 'categories', 'imagecompte'));
            } else {
                abort(404);
            }
        }
    }

    public function details($service_url)
    {

        $userIsAuthenticated = auth()->check();
        if ($userIsAuthenticated == null) {
            $imagecompte = [];
        } else {
            $imagecompte = auth()->user()->image;
        }
        $productDetails = Service::with('categories')->where('service_url', $service_url)->first();
        if ($productDetails == null) {
            abort(404);
        } else {
            $productDetails = Service::with('categories')->where('service_url', $service_url)->first();

            $services = Service::select('credential')->where('service_url', $service_url)->first();

            $service = json_decode($services);

            $bundles = $service->credential->bundle;

            $credential = $service->credential;

            $categorie_id = Service::with('categories')->where('service_url', $service_url)->value('categorie_id');

            $category = Categorie::select('url', 'nom_categorie')->where('id', $categorie_id)->first();

            $otherservices = Service::with('categories')
                ->where('categorie_id', $categorie_id)
                ->where('service_url', '!=', $service_url)
                ->where('status', 0)
                ->limit(4)
                ->get();

            $relatedProducts = Service::limit(4)->inRandomOrder()->where('service_url', '!=', $service_url)->where('status', 0)->get()->toArray();

            // dd($credential);

            return view('web.detail')->with(compact('productDetails', 'services', 'bundles', 'userIsAuthenticated', 'credential', 'otherservices', 'relatedProducts', 'category', 'imagecompte'));
        }
    }

    public function bundle()
    {
        return view('web.bundle');
    }

    public function demandeWithOtp(Request $request)
    {
        try {
            // Obtenir l'url 
            $serviceurl = Service::select('credential')->where('nom_service', $request->service_name)->first();

            $apiURL = $serviceurl->credential['url_confirmation_abonnement'];

            // Headers
            $headers = [
                'xuser:' . $request->xuser,
                'xtoken:' . $request->xtoken,
                'content-type: application/json'
            ];

            // POST Data
            $postInput = [
                'transaction_id' => $request->transactionid,
                'code_otp' => $request->otp,
            ];


            $info = Transaction::where('transactionid', $request->transactionid)->first();

            $contact = $info['msisdn'];
            $forfait = $info['forfait'];
            $nom_service = $info['nom_service'];
            $service_name = $info['service_name'];
            $user_id = $info['user_id'];
            $service_id = $info['service_id'];
            $partenaire_id = $info['partenaire_id'];
            $amount = $info['amount'];
            $image = $info['image'];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiURL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postInput));
            $result = curl_exec($ch);
            curl_close($ch);
            Log::info('Confirmation demande :', ['data' => $result]);

            $date = json_decode($result);

            if($date->statusCode == "2032"){
                $request->session()->flash('error', 'Votre crédit est inssuffisant pour souscrire à cette offre');

                return redirect()->away('/');
            }

            if($date->statusCode == "2061"){
                
                $request->session()->flash('error', 'Cette operation a déjà été prise en compte.');

                return redirect()->away('/');      
            }

            if($date->statusCode == "2084"){
                
                $request->session()->flash('error', 'Vous êtes déjà inscrit ou abonné au service demandé.');

                return redirect()->away('/');      
            }

            // dd($date);

            $date_fin_abonnement = $date->date_fin_abonnement;

            // Mise à jour de la transaction
            Transaction::where('transactionid', $request->transactionid)->update(['date_fin_abonnement' => $date_fin_abonnement, 'status' => "successful", 'etat' => 1]);

            //verifier si le client est un abonné
            $abonne = Abonne::where('msisdn', $contact)->where('service_id', $service_id)->count();
            // dd($abonne);
            if ($abonne == 1) {
                return redirect('/compte');
                // return redirect()->route('listing');
            }

            // Enregistré l'abonné
            $order = new Abonne();
            $order->nom_service = $nom_service;
            $order->service_name = $service_name;
            $order->msisdn = $contact;
            $order->forfait = $forfait;
            $order->amount = $amount;
            $order->image = $image;
            $order->transactionid = $request->transactionid;
            $order->user_id = $user_id;
            $order->service_id = $service_id;
            $order->partenaire_id = $partenaire_id;
            $order->date_abonnement = date("Y-m-d");
            $order->date_fin_abonnement = $date_fin_abonnement;
            $order->save();

            // return redirect()->view('listing');
            return redirect('/compte');
        } catch (\Exception $exception) {

            $request->session()->flash('message', 'Veuillez ressayez plus tard');

            return redirect()->back();
        }
    }
}
