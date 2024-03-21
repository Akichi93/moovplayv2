<?php

namespace App\Http\Controllers\Front;

use Log;
use App\Models\User;
use App\Models\Abonne;
use App\Models\Service;
use App\Models\Categorie;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;
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


    public function demandeService(Request $request)
    {
        $rules = [
            'contact' => 'required|digits:10',
            'service_id' => 'required',
            // 'nom_service' => 'required',
            'forfait' => 'required',
            // 'image' => 'required',
            'amount' => 'required',
            'mode_paiement' => 'required',
        ];

        $customMessage = [
            'contact.required' => 'Entrez le numéro de téléphone',
            'service_id.required' => 'Entrez le service',
            // 'nom_service.required' => 'Entrez le partenaire',
            'forfait.required' => 'Le forfait n\'est pas defini',
            // 'image.required' => 'L\'image n\'est pas defini',
            'amount.required' => 'Le montant n\'est pas defini',
            'mode_paiement.required' => 'Le type de paiment est requis',
        ];
        $this->validate($request, $rules, $customMessage);

        try {



            if ($request->mode_paiement != "AIR_TIME") {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mode de paiement est invalide.',
                ], Response::HTTP_OK);
            }

            // vérifier cenuméro existe dans la bdd
            $numberExist = DB::table('users')->where('contact', $request->contact)->count();
            if ($numberExist == 0) {
                $verification_code = random_int(1000, 9999);

                // Ajouter le numéro dans la bdd
                $user = new User();
                $user->code = $verification_code;
                $user->contact = $request->contact;
                $user->referent = Str::random(6);
                $user->save();

                $day = date('d');
                $month = date('m');
                $year = date('Y');
                $a = "MP";
                $heure = date("h:i:sa");

                $ref = $a . '-' . intval($month) . intval($day) . $year . $heure;

                $order_url =  Crypt::encrypt($ref);


                $partenaire_id = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                    ->where('services.id', $request->service_id)
                    ->value('partenaire_id');

                $service_name = Service::select('credential')->where('id', $request->service_id)->first();

                $service = json_decode($service_name);

                $servicename = $service->credential->service_name;

                // Récupération du nom du service et l'image

                $nomService = Service::select('nom_service')->where('id', $request->service_id)->value('nom_service');

                $imageService = Service::where('id', $request->service_id)->value('image');

                Log::info('Demande :', ['data' => $nomService]);
                // Log::info('Demande :', ['data' => $imageService]);


                $contact = $request->contact;

                $mobile = '225' . $contact;

                // Obtenir le xuser et le xtoken
                $xuser = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                    ->where('services.id', $request->service_id)
                    ->value('x_user');

                $xtoken = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                    ->where('services.id', $request->service_id)
                    ->value('x_token');

                // Obtenir l'url 
                $serviceurl = Service::select('credential')->where('id', $request->service_id)->first();

                $apiURL = $serviceurl->credential['url_demande_abonnement'];

                if ($apiURL == null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce service est indisponible.',
                    ], Response::HTTP_OK);
                }

                // Headers
                $headers = [
                    'xuser:' . $xuser,
                    'xtoken:' . $xtoken,
                    'content-type: application/json'
                ];

                // POST Data
                $postInput = [
                    'forfait' => $request->forfait,
                    'amount' => $request->amount,
                    'msisdn' => $mobile,
                    'order_id' => $ref,
                    'service_name' => $servicename
                ];



                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiURL);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postInput));
                $result = curl_exec($ch);
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                    Log::info('Demande :', ['data' => $result]);

                    $info = json_decode($result);

                    $status = $info->statusCode;

                    if ($status == "2032") {

                        return response()->json([
                            'success' => false,
                            'message' => 'Le solde de l\'abonné est insuffisant.',
                        ], Response::HTTP_OK);
                    } else if ($status == "2084") {

                        return response()->json([
                            'success' => false,
                            'message' => 'Vous êtes déjà inscrit ou abonné au service demandé.',
                        ], Response::HTTP_OK);
                    } else if ($status == "2061") {

                        return response()->json([
                            'success' => false,
                            'message' => 'Veuillez ressayer plus tard.',
                        ], Response::HTTP_OK);
                    }

                    // Ajouter la transaction dans la bdd
                    $order = new Transaction();
                    $order->order_id = $ref;
                    $order->user_id = $user->id;
                    $order->service_id = $request->service_id;
                    $order->partenaire_id = $partenaire_id;
                    $order->nom_service = $nomService;
                    $order->forfait = $request->forfait;
                    $order->amount = $request->amount;
                    $order->msisdn = $mobile;
                    $order->service_name = $servicename;
                    $order->order_url = $order_url;
                    $order->image = $imageService;
                    $order->canal = "web";
                    $order->mode_paiement = $request->mode_paiement;
                    $order->save();

                    $lastID = $order->id;

                    $transaction = json_decode($result);


                    $transaction_id = $transaction->transaction_id;


                    // Mise à jour de la transaction
                    Transaction::where('id', $lastID)->update(['transactionid' => $transaction_id, 'xuser' => $xuser, 'xtoken' => $xtoken]);

                    return response()->json([
                        'success' => true,
                        'message' => 'La souscription a été bien effectuée.',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service non disponible',
                    ], Response::HTTP_OK);
                }
                curl_close($ch);
            } else {
                $day = date('d');
                $month = date('m');
                $year = date('Y');
                $a = "MP";
                $heure = date("h:i:sa");

                $ref = $a . '-' . intval($month) . intval($day) . $year . $heure;

                $order_url =  Crypt::encrypt($ref);


                $partenaire_id = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                    ->where('services.id', $request->service_id)
                    ->value('partenaire_id');

                $service_name = Service::select('credential')->where('id', $request->service_id)->first();


                $service = json_decode($service_name);

                $servicename = $service->credential->service_name;

                // Récupération du nom du service et l'image

                $nomService = Service::where('id', $request->service_id)->value('nom_service');

                $imageService = Service::where('id', $request->service_id)->value('image');



                $contact = $request->contact;

                $mobile = '225' . $contact;

                // Obtenir le xuser et le xtoken
                $xuser = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                    ->where('services.id', $request->service_id)
                    ->value('x_user');

                $xtoken = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                    ->where('services.id', $request->service_id)
                    ->value('x_token');

                // Obtenir l'url 
                $serviceurl = Service::select('credential')->where('id', $request->service_id)->first();

                $apiURL = $serviceurl->credential['url_demande_abonnement'];

                if ($apiURL == null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce service est indisponible.',
                    ], Response::HTTP_OK);
                }

                //verifier si l'utilisateur à dejà un abonnement à ce service 

                // $auth = Transaction::where('service_id', $request->service_id)->where('user_id', Auth::user()->id)->count();
                // Headers
                $headers = [
                    'xuser:' . $xuser,
                    'xtoken:' . $xtoken,
                    'content-type: application/json'
                ];

                // POST Data
                $postInput = [
                    'forfait' => $request->forfait,
                    'amount' => $request->amount,
                    'msisdn' => $mobile,
                    'order_id' => $ref,
                    'service_name' => $servicename
                ];



                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiURL);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postInput));
                $result = curl_exec($ch);
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                    Log::info('Demande :', ['data' => $result]);

                    $info = json_decode($result);

                    $status = $info->statusCode;

                    if ($status == "2032") {

                        return response()->json([
                            'success' => false,
                            'message' => 'Le solde de l\'abonné est insuffisant.',
                        ], Response::HTTP_OK);
                    } else if ($status == "2084") {

                        return response()->json([
                            'success' => false,
                            'message' => 'Vous êtes déjà inscrit ou abonné au service demandé.',
                        ], Response::HTTP_OK);
                    } else if ($status == "2061") {

                        return response()->json([
                            'success' => false,
                            'message' => 'Veuillez ressayer plus tard.',
                        ], Response::HTTP_OK);
                    }

                    $id = User::where('contact', $request->contact)->value('id');

                    // Ajouter la transaction dans la bdd
                    $order = new Transaction();
                    $order->order_id = $ref;
                    $order->user_id = $id;
                    $order->service_id = $request->service_id;
                    $order->partenaire_id = $partenaire_id;
                    $order->nom_service = $nomService;
                    $order->forfait = $request->forfait;
                    $order->amount = $request->amount;
                    $order->msisdn = $mobile;
                    $order->service_name = $servicename;
                    $order->order_url = $order_url;
                    $order->image = $imageService;
                    $order->canal = "web";
                    $order->mode_paiement = $request->mode_paiement;
                    $order->save();

                    $lastID = $order->id;

                    $transaction = json_decode($result);

                    $transaction_id = $transaction->transaction_id;


                    // Mise à jour de la transaction
                    Transaction::where('id', $lastID)->update(['transactionid' => $transaction_id, 'xuser' => $xuser, 'xtoken' => $xtoken]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Veuillez consulter votre messagerie.',
                        'data' => $info,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce service est indisponible.',
                    ], Response::HTTP_OK);
                }
                curl_close($ch);
            }
        } catch (\Exception $exception) {
            Log::info('error :', ['data' => $exception]);
            return response()->json([
                'success' => false,
                'message' => 'Veuillez ressayez plus tard.',
            ], Response::HTTP_OK);
        }
    }

    public function demandeWithOtp(Request $request)
    {
        $rules = [
            'code_otp' => 'required',
            'transaction_id' => 'required',

        ];

        $customMessage = [
            'code_otp.required' => 'Entrez le numéro de téléphone',
            'transaction_id.required' => 'Entrez le numéro de la transaction',
        ];
        $this->validate($request, $rules, $customMessage);

        try {
            // Récupérer le service_name
            $serviceName = Transaction::select('nom_service')->where('transactionid', $request->transaction_id)->value('nom_service');

            $xuser = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                ->where('services.nom_service', $serviceName)
                ->value('x_user');


            $xtoken = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
                ->where('services.nom_service', $serviceName)
                ->value('x_token');

            // Obtenir l'url 
            $serviceurl = Service::select('credential')->where('nom_service', $serviceName)->first();

            $apiURL = $serviceurl->credential['url_confirmation_abonnement'];

            // Headers
            $headers = [
                'xuser:' . $xuser,
                'xtoken:' . $xtoken,
                'content-type: application/json'
            ];

            // POST Data
            $postInput = [
                'transaction_id' => $request->transaction_id,
                'code_otp' => $request->code_otp,
            ];



            $info = Transaction::where('transactionid', $request->transaction_id)->first();

            Log::info('info :', ['data' => $info]);

            $contact = $info['msisdn'];
            $forfait = $info['forfait'];
            $nom_service = $info['nom_service'];
            $service_name = $info['service_name'];
            $user_id = $info['user_id'];
            $service_id = $info['service_id'];
            $partenaire_id = $info['partenaire_id'];
            $amount = $info['amount'];
            $image = $info['image'];

            Log::info($info);



            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiURL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postInput));
            $result = curl_exec($ch);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                $data = json_decode($result);

                Log::info('erreur :', ['data' => $result]);
                if ($data->statusCode === "0") {

                    $date_fin_abonnement = $data->date_fin_abonnement;

                    // Mise à jour de la transaction
                    Transaction::where('transactionid', $request->transaction_id)->update(['date_fin_abonnement' => $date_fin_abonnement, 'status' => "successful", 'etat' => 1]);

                    $verif = Abonne::where('msisdn', $contact)->where('nom_service', $nom_service)->count();
                    if ($verif == 0) {
                        // Enregistré l'abonné
                        $abonne = new Abonne();
                        $abonne->nom_service = $nom_service;
                        $abonne->service_name = $service_name;
                        $abonne->msisdn = $contact;
                        $abonne->forfait = $forfait;
                        $abonne->amount = $amount;
                        // $order->image = $image;
                        $abonne->transactionid = $request->transaction_id;
                        $abonne->user_id = $user_id;
                        $abonne->service_id = $service_id;
                        $abonne->partenaire_id = $partenaire_id;
                        $abonne->date_abonnement = date("Y-m-d");
                        $abonne->date_fin_abonnement = $date_fin_abonnement;
                        $abonne->save();

                        $msisdn = substr($contact, 3);
                        $user = User::where('id', $user_id)->get();

                        $contact = User::where('contact', $msisdn)->first();

                        $token = Auth::login($contact);

                        return response()->json([
                            'success' => true,
                            'message' => 'Souscription effectué avec succès.',
                            'user' => $user,
                            'authorization' => [
                                'token' => $token,
                                'type' => 'bearer',
                            ]
                        ], Response::HTTP_OK);
                    } else {

                        Abonne::where('msisdn', $contact)->where('nom_service', $nom_service)->update(['forfait' => $forfait, 'amount' => $amount, 'transactionid' => $request->transaction_id, 'date_desabonnement' => null]);

                        $msisdn = substr($contact, 3);
                        $user = User::where('id', $user_id)->get();

                        $contact = User::where('contact', $msisdn)->first();

                        $token = Auth::login($contact);

                        return response()->json([
                            'success' => true,
                            'message' => 'Souscription effectué avec succès.',
                            'user' => $user,
                            'authorization' => [
                                'token' => $token,
                                'type' => 'bearer',
                            ]
                        ], Response::HTTP_OK);
                    }
                } else if ($data->statusCode == "2032") {
                    return response()->json([
                        'success' => false,
                        'message' => 'Votre crédit est insuffisant pour souscrire à cette offre.',
                    ], Response::HTTP_OK);
                } else if ($data->statusCode == "2061") {

                    return response()->json([
                        'success' => false,
                        'message' => 'Requête invalide.',
                    ], Response::HTTP_OK);
                } else if ($data->statusCode == "2084") {

                    return response()->json([
                        'success' => false,
                        'message' => 'Vous êtes déjà inscrit ou abonné au service demandé.',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service indisponible veuillez ressayez plus tard.',
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service est indisponible.',
                ], Response::HTTP_OK);
            }
            curl_close($ch);
        } catch (\Exception $exception) {
            Log::info('erreur :', ['data' => $exception]);
            return response()->json([
                'success' => false,
                'message' => 'Service indisponible veuillez ressayez plus tard.',
            ], Response::HTTP_OK);
        }
    }

    public function desAbonnement(Request $request)
    {
        $rules = [
            'transaction_id' => 'required',

        ];

        $customMessage = [

            'transaction_id.required' => 'Entrez le numéro de la transaction',
        ];
        $this->validate($request, $rules, $customMessage);
        // try {

        $serviceName = Transaction::select('nom_service')->where('transactionid', $request->transaction_id)->value('nom_service');


        // Obtenir l'url 
        $serviceurl = Service::select('credential')->where('nom_service', $serviceName)->first();

        $apiURL = $serviceurl->credential['url_desabonnement'];


        $xuser = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
            ->where('services.nom_service', $serviceName)
            ->value('x_user');


        $xtoken = Service::join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')
            ->where('services.nom_service', $serviceName)
            ->value('x_token');

        // Headers
        $headers = [
            'xuser:' . $xuser,
            'xtoken:' . $xtoken,
            'content-type: application/json'
        ];

        // POST Data
        $postInput = [
            'transaction_id' => $request->transaction_id,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postInput));
        $result = curl_exec($ch);
        curl_close($ch);
        Log::info('Desabonnement :', ['data' => $result]);

        $date =  date("Y-m-d");

        //  Mise à jour de la transaction
        Transaction::where('transactionid', $request->transaction_id)->update(['date_desabonnement' => $date, 'etat' => 'Desabonnement']);

        // Mise à jour de la table abonné

        Abonne::where('transactionid', $request->transaction_id)->update(['date_desabonnement' => date("Y-m-d")]);

        return response()->json([
            'success' => true,
            'message' => 'Vous êtes désabonnés de ce service.',
        ], Response::HTTP_OK);
        // } catch (\Exception $exception) {

        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Erreur.',
        //     ], Response::HTTP_OK);
        // }
    }
}
