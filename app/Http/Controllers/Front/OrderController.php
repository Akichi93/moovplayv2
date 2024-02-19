<?php

namespace App\Http\Controllers\Front;

use Log;
use App\Models\Service;
use App\Models\Transaction;
// use DinhQuocHan\Twig\Extensions\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class OrderController extends Controller
{
    public function demandeService(Request $request)
    {
        $rules = [
            'service_id' => 'required',
            'nom_service' => 'required',
            'forfait' => 'required',
            'image' => 'required',
            'amount' => 'required',
        ];

        $customMessage = [
            'service_id.required' => 'Entrez le service',
            'nom_service.required' => 'Entrez le partenaire',
            'forfait.required' => 'Le forfait n\'est pas defini',
            'image.required' => 'L\'image n\'est pas defini',
            'amount.required' => 'Le montant n\'est pas defini',
        ];
        $this->validate($request, $rules, $customMessage);

        try {

            $user = Auth::user()->id;
            if ($user == null) {
                return back();
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



                $contact = Auth::user()->contact;

                $mobile = '225' . $contact;
                // dd($mobile);


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

                //verifier si l'utilisateur à dejà un abonnement à ce service 

                $auth = Transaction::where('service_id', $request->service_id)->where('user_id', Auth::user()->id)->count();
                // if ($auth == 1) {
                //     $request->session()->flash('error', 'Vous êtes déjà inscrit ou abonné au service demandé');

                //     return redirect()->back();
                // }

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
                curl_close($ch);
                Log::info('Demande :', ['data' => $result]);

                $info = json_decode($result);

                $status = $info->statusCode;

                // dd($status);

                if ($status == "2032") {
                    $request->session()->flash('error', 'Le solde de l\'abonné est insuffisant');

                    return redirect()->back();
                } else if ($status == "2084") {

                    $request->session()->flash('error', 'Vous êtes déjà inscrit ou abonné au service demandé');

                    return redirect()->back();
                }  else if($status == "2061"){
                    $request->session()->flash('error', 'Veuillez ressayer plus tard');
                }

                // Ajouter la transaction dans la bdd
                $order = new Transaction();
                $order->order_id = $ref;
                $order->user_id = Auth::user()->id;
                $order->service_id = $request->service_id;
                $order->partenaire_id = $partenaire_id;
                $order->nom_service = $request->nom_service;
                $order->forfait = $request->forfait;
                $order->amount = $request->amount;
                $order->msisdn = $mobile;
                $order->service_name = $servicename;
                $order->order_url = $order_url;
                $order->image = $request->image;
                // $order->transaction_method = "web";
                $order->save();

                $lastID = $order->id;

                $transaction = json_decode($result);


                $transaction_id = $transaction->transaction_id;

                $request->session()->flash('souscription', 'La souscription a été bien effectuée');

                // Mise à jour de la transaction
                Transaction::where('id', $lastID)->update(['transactionid' => $transaction_id, 'xuser' => $xuser, 'xtoken' => $xtoken]);

                return redirect()->route('demandeotp', ['order_url' => $order_url]);
            }
        } catch (\Exception $exception) {
            $request->session()->flash('message', 'Veuillez ressayez plus tard');

            return redirect()->back();
        }
    }

    public function demandeotp($order_url)
    {
        $userIsAuthenticated = auth()->check();
        if ($userIsAuthenticated == null) {
            $imagecompte = [];
        } else {
            $imagecompte = auth()->user()->image;
        }
        $services = Transaction::where('order_url', $order_url)->first();

        return view('web.otp')->with(compact('services', 'userIsAuthenticated', 'imagecompte'));
    }
}
