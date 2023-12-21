<?php

namespace App\Http\Controllers\Front;

use Log;
use App\Models\User;
use App\Models\Client;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Abonne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class ClientController extends Controller
{
    public function userLoginRegister()
    {
        $userIsAuthenticated = auth()->check();
        return view('web.register')->with(compact('userIsAuthenticated'));
    }

    public function register(Request $request)
    {

        $rules = [
            'contact' => 'required|numeric|digits:10',
        ];

        $customMessage = [
            'contact.required' => 'Entrez votre contact svp',
            'contact.numeric' => 'Le format du numéro de téléphonne incorrect',
            'contact.digits' => 'Entrez un numéro de téléphone de 10 chiffres.',
        ];

        $this->validate($request, $rules, $customMessage);

        // dd($request->all());
        if ($request->isMethod('post')) {
            $data = $request->all();
            /*echo "<pre>"; print_r($data); die;*/

            // Verifier si le numéro renseigner est un numéro moov
            $number = $data['contact'];

            $def = substr($number, 0, 2);  // abcd

            if ($def != "01") {
                $request->session()->flash('message', "Veuillez entrer un numéro moov.");
                return back();
            } else {
                // Check if User already exists
                // $usersCount = User::where('contact', $data['contact'])->where('status', 1)->count();
                // if ($usersCount > 0) {
                //     return back()->with('flash_message_error', 'Ce numero existe déja!');
                // } else {

                $getNumber = User::where('contact', $request->contact)->count();
                if ($getNumber == 0) {
                    //Generate verification code
                    $verification_code = random_int(1000, 9999);

                    // Ajouter le numéro dans la bdd
                    $user = new User();
                    $user->code = $verification_code;
                    $user->contact = $request->contact;
                    $user->save();

                    $lastID = User::max('id');

                    $Dernier = $user->id;

                    $contact = $request->all();

                    $otp =  User::where('contact', $request->contact)->pluck('code')->first();
                    $message = "Votre code d'inscription MOOVPLAY est : $otp";
                    $now = date('Y-m-d H:i:s');

                    $mobile = '225' . $request->contact;

                    $message = [
                        "code_service" => "IZYPHONE",
                        "password" => "00225izyphone",
                        "message" => $message,
                        "sender" => '98096',
                        "msisdn" => $mobile,
                        "datetime" => $now,
                    ];

                    $json = json_encode($message);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://smartdev.ci/sms/smsMT.php");
                    curl_setopt($ch, CURLOPT_POST, 1);

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $server_output = curl_exec($ch);
                    curl_close($ch);

                    Log::info('Demande :', ['data' => $server_output]);

                    $prodID = Crypt::encrypt($lastID);

                    return redirect()->route('verification', ['id' => $prodID]);

                    // return view('web.verification')->with('user_id', $lastID); 
                } else {
                    //Generate verification code
                    $verification_code = random_int(1000, 9999);

                    // Mise à jour de l'otp
                    DB::table('users')->where('contact', $request->contact)->update(['code' => $verification_code]);

                    // Obtenir les infos de l'utilisateurs

                    $user = User::select('id')->where('contact', $request->contact)->first();

                    // Envoie de SMS
                    $otp =  User::where('contact', $request->contact)->pluck('code')->first();
                    $message = "Votre code  d'inscription MOOVPLAY est : $otp";

                    $now = date('Y-m-d H:i:s');

                    $mobile = '225' . $request->contact;

                    $message = [
                        "code_service" => "IZYPHONE",
                        "password" => "00225izyphone",
                        "message" => $message,
                        "sender" => '98096',
                        "msisdn" => $mobile,
                        "datetime" => $now,
                    ];

                    $json = json_encode($message);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://smartdev.ci/sms/smsMT.php");
                    curl_setopt($ch, CURLOPT_POST, 1);

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $server_output = curl_exec($ch);
                    curl_close($ch);

                    Log::info('Demande :', ['data' => $server_output]);

                    $lastID = $user->id;

                    $prodID = Crypt::encrypt($lastID);

                    return redirect()->route('verification', ['id' => $prodID]);
                }
            }
        }
        // }
    }

    public function loginclient(Request $request)
    {
        $data = $request->all();

        $rules = [
            'contact' => 'required|numeric|digits:10',
        ];

        $customMessage = [
            'contact.required' => 'Entrez votre contact svp',
            'password.numeric' => 'Le format du numéro de téléphonne incorrect',
            'password.digits' => 'Entrez un numéro de téléphone de 10 chiffres.',
        ];

        $this->validate($request, $rules, $customMessage);

        // Verifier si 
        $verif = User::select('status')->where('contact', $request->input('contact'))->first();

        // dd($verif->status);

        if ($verif->status == 0) {
            $request->session()->flash('loginerror', 'Veuillez valider votre compte');
            return redirect()->back();
        }

        $user = User::where('contact', $request->contact)->first();

        if ($user) {
            $request->session()->flash('login', 'Vous êtes à nouveau connecté');
            Auth::login($user);
        } else {
            $request->session()->flash('loginerror', 'Ce numéro n\'existe pas dans notre base');
            return redirect()->back();
        }


        return redirect("/")->withSuccess('Oppes! You have entered invalid credentials');
    }

    public function compte()
    {
        $userIsAuthenticated = auth()->user();
        $imagecompte = auth()->user()->image;
        $services = Abonne::where('user_id', Auth::user()->id)->where('date_desabonnement', '=', null)
            // ->where('etat', '!=', 'Desabonnement')
            ->orderBy('id', 'desc')
            ->get();

        // dd($services);

        return view('web.compte')->with(compact('userIsAuthenticated', 'services', 'imagecompte'));
    }

    public function profil()
    {
        $userIsAuthenticated = auth()->user();
        if ($userIsAuthenticated == null) {
            $imagecompte = [];
        } else {
            $imagecompte = auth()->user()->image;
        }
        $users = User::where('id', Auth::user()->id)->first();
        return view('web.profil')->with(compact('userIsAuthenticated', 'users', 'imagecompte'));
    }

    public function logoutclient()
    {
        Auth::logout();
        return redirect('/');
    }

    public function verification($id)
    {
        $ids = Crypt::decrypt($id);
        $numero = User::select('contact')->where('id', $ids)->first();
        return view('web.verification')->with([
            'numero' => $numero
        ]);;
    }

    public function loginWithOtp(Request $request)
    {
        $data = $request->all();

        $rules = [
            'contact' => 'required',
        ];

        $customMessage = [
            'contact.required' => 'contact est requis',
        ];
        $this->validate($request, $rules, $customMessage);

        #Validation Logic
        $verificationCode  = User::where('contact', $request->contact)->where('code', $request->otp)->first();

        if (!$verificationCode) {
            return redirect()->back()->with('error', 'Le code est incorrect. Veuillez renseigner le bon code');
        }

        $user = User::where('contact', $request->contact)->first();
        if ($user) {
            // Expire The OTP

            User::where('contact', $request->contact)->update(['status' => 1]);

            Auth::login($user);

            return redirect('/compte');
        }
    }

    public function resetWithOtp(Request $request)
    {
        $data = $request->all();

        $rules = [
            'contact' => 'required',
            'otp' => 'required',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required'
        ];

        $customMessage = [
            'contact.required' => 'Email is required',
            'otp.required' => 'Veuillez enter le code otp',
            'password.required' => 'Veuillez entrer un mot de passe',
            'password_confirmation' => 'Veuillez entrer le mot de passe pour confirmation '
        ];
        $this->validate($request, $rules, $customMessage);

        #Validation Logic
        $verificationCode  = User::where('contact', $request->contact)->where('code', $request->otp)->first();

        if (!$verificationCode) {
            $request->session()->flash('message', 'Entrez le bon code !');
            return redirect()->back();
        }

        User::where('contact', $request->contact)->update(['password' =>  Hash::make($request->password)]);


        $request->session()->flash('resend', "Mot de passe réinitialisé avec succès.");


        return redirect('/');
    }

    public function updateinfo(Request $request)
    {
        $userIsAuthenticated = auth()->user();

        if ($request->isMethod('post')) {
            if ($request->file != null) {

                $fileNameWithTheExtension = request('file')->getClientOriginalName();

                //obtenir le nom de l'image

                $fileName = pathinfo($fileNameWithTheExtension, PATHINFO_FILENAME);

                // extension
                $extension = request('file')->getClientOriginalExtension();

                // creation de nouveau nom
                $newFileName = $fileName . '_' . time() . '.' . $extension;

                $path = request('file')->move('image/user_images', $newFileName);

                User::where('id', Auth::user()->id)->update(['name' => $request->name, 'prenom' => $request->prenom, 'email' => $request->email, 'image' => $newFileName]);
                $request->session()->flash('message', 'Votre profil à été mis à jour ave succès');
            }

            User::where('id', Auth::user()->id)->update(['name' => $request->name, 'prenom' => $request->prenom, 'email' => $request->email]);

            $request->session()->flash('message', 'Votre profil à été mis à jour ave succès');
            return redirect()->back();
        }
    }

    public function changepassword()
    {
        $userIsAuthenticated = auth()->user();

        return view('web.updatepassword')->with(compact('userIsAuthenticated'));
    }

    public function forgot(Request $request)
    {

        $rules = [
            'contact' => 'required',
        ];

        $customMessage = [
            'contact.required' => 'Entrez votre contact svp',
        ];
        $this->validate($request, $rules, $customMessage);

        if ($request->isMethod('post')) {
            $data = $request->all();
            $contact = User::where('contact', $data['contact'])->count();
            if ($contact == 0) {
                $request->session()->flash('error', "Ce contact n'existe pas.");
                return redirect()->back();
            } else {
                //Generate verification code
                $verification_code = random_int(1000, 9999);

                User::where('contact', $request->contact)->update(['code' => $verification_code]);

                $get =  User::where('contact', $request->contact)->pluck('id')->first();



                $contact = $request->all();

                $otp =  User::where('contact', $request->contact)->pluck('code')->first();
                $message = "Votre code OTP est : $otp";
                $now = date('Y-m-d H:i:s');



                $mobile = '225' . $request->contact;

                $message = [
                    "code_service" => "IZYPHONE",
                    "password" => "00225izyphone",
                    "message" => $message,
                    "sender" => '98096',
                    "msisdn" => $mobile,
                    "datetime" => $now,
                ];

                $json = json_encode($message);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://smartdev.ci/sms/smsMT.php");
                curl_setopt($ch, CURLOPT_POST, 1);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $server_output = curl_exec($ch);
                curl_close($ch);

                Log::info('Demande :', ['data' => $server_output]);

                $lastID = User::where('contact', $request->contact)->value('id');

                $prodID = Crypt::encrypt($lastID);

                return redirect()->route('verificationreset', ['id' => $prodID]);
            }
        }
    }

    public function resetPassword()
    {
        return view('web.resetpassword');
    }

    public function updatepassword(Request $request)
    {
        $rules = [
            'oldpassword' => 'required',
            'newpassword' => 'required',
        ];

        $customMessage = [
            'oldpassword.required' => 'Entrez l\'ancien mot de passe',
            'newpassword.required' => 'Entrez le nouveau mot de passe',
        ];
        $this->validate($request, $rules, $customMessage);

        $hashedPassword = Auth::user()->password;

        if (Hash::check($request->oldpassword, $hashedPassword)) {

            if (!Hash::check($request->newpassword, $hashedPassword)) {

                $users = User::find(Auth::user()->id);
                $users->password = bcrypt($request->newpassword);
                User::where('id', Auth::user()->id)->update(array('password' => $users->password));


                $request->session()->flash('message', 'Le nouveau mot de passe ne peut pas être l\'ancien mot de passe !');
                // session()->flash('message', 'le mot de passe a été mis à jour avec succès');

                return redirect()->back();
            } else {
                $request->session()->flash('message', 'Le nouveau mot de passe ne peut pas être l\'ancien mot de passe !');
                return redirect()->back();
            }
        } else {
            $request->session()->flash('message', 'L\'ancien mot de passe ne correspond pas');
            return redirect()->back();
        }
    }

    public function verificationreset($id)
    {
        $ids = Crypt::decrypt($id);
        $numero = User::select('contact')->where('id', $ids)->first();
        return view('web.verificationreset')->with([
            'numero' => $numero
        ]);
    }

    public function desabonnement(Request $request)
    {


        try {
            // Obtenir l'url 
            $serviceurl = Service::select('credential')->where('nom_service', $request->service_name)->first();

            $apiURL = $serviceurl->credential['url_desabonnement'];

            // Headers
            $headers = [
                'Xuser:' . $request->xuser,
                'Xtoken:' . $request->xtoken,
                'content-type: application/json'
            ];

            // POST Data
            $postInput = [
                'transaction_id' => $request->transactionid,
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
            Transaction::where('transactionid', $request->transactionid)->update(['date_desabonnement' => $date, 'etat' => 'Desabonnement']);

            // Mise à jour de la table abonné

            Abonne::where('transactionid', $request->transactionid)->update(['date_desabonnement' => date("Y-m-d")]);

            return redirect()->back();
        } catch (\Exception $exception) {

            $request->session()->flash('message', 'Veuillez ressayez plus tard');

            return redirect()->back();
        }
    }

    public function resendOtp(Request $request)
    {
        $rules = [
            'contact' => 'required',
        ];

        $customMessage = [
            'contact.required' => 'Entrez votre contact svp',
        ];
        $this->validate($request, $rules, $customMessage);

        if ($request->etape == "Inscription") {
            //Generate verification code
            $verification_code = random_int(1000, 9999);

            // Mise à jour de l'otp
            DB::table('users')->where('contact', $request->contact)->update(['code' => $verification_code]);

            // Obtenir les infos de l'utilisateurs

            $user = User::select('id')->where('contact', $request->contact)->first();

            // Envoie de SMS
            $otp =  User::where('contact', $request->contact)->pluck('code')->first();
            $message = "Votre code  d'inscription MOOVPLAY est : $otp";

            $now = date('Y-m-d H:i:s');

            $mobile = '225' . $request->contact;

            $message = [
                "code_service" => "IZYPHONE",
                "password" => "00225izyphone",
                "message" => $message,
                "sender" => '98096',
                "msisdn" => $mobile,
                "datetime" => $now,
            ];

            $json = json_encode($message);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://smartdev.ci/sms/smsMT.php");
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);
            curl_close($ch);

            Log::info('Demande :', ['data' => $server_output]);

            $request->session()->flash('envoie', 'Le code à été envoyé');

            return back();
        }
    }

    public function uploadfile(Request $request)
    {
        dd($request->all());
    }
}
