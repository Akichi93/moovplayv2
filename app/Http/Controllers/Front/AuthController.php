<?php

namespace App\Http\Controllers\Front;

use Exception;
use App\Models\User;
use App\Models\Abonne;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Question;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }



    public function register(Request $request)
    {
        $data = $request->all();
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


            // Verifier si le numéro renseigner est un numéro moov
            $number = $data['contact'];

            $def = substr($number, 0, 2);  // abcd

            if ($def != "01") {

                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez entrer un numéro moov.',
                ], Response::HTTP_OK);
            } else {
                // Check if User already exists
                // $usersCount = User::where('contact', $data['contact'])->where('status', 1)->count();
                // if ($usersCount > 0) {
                //     return back()->with('flash_message_error', 'Ce numero existe déja!');
                // } else {

                $getNumber = DB::table('users')->where('contact', $request->contact)->count();

                if ($getNumber == 0) {
                    //Generate verification code
                    $verification_code = random_int(1000, 9999);

                    // Ajouter le numéro dans la bdd
                    $user = new User();
                    $user->code = $verification_code;
                    $user->contact = $request->contact;
                    $user->referent = Str::random(6);
                    $user->save();

                    $lastID = DB::table('users')->max('id');

                    $Dernier = $user->id;

                    $contact = $request->all();

                    $otp =  DB::table('users')->where('contact', $request->contact)->pluck('code')->first();

                    $message = "Votre code de connexion à MOOVPLAY est : $otp";
                    $now = date('Y-m-d H:i:s');

                    $mobile = '225' . $request->contact;

                    $message = [
                        "code_service" => "MOOVPLAY",
                        "password" => "zpkd8547@moovplay-2024",
                        "message" => $message,
                        "sender" => 'MOOVPLAY',
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



                    return response()->json([
                        'success' => true,
                        'message' => 'Veuillez vérifier votre messagerie pour terminer.',
                        'user' => $user,
                        'otp' => $otp
                    ], Response::HTTP_OK);

                    // return view('web.verification')->with('user_id', $lastID); 
                } else {
                    //Generate verification code
                    $verification_code = random_int(1000, 9999);

                    // Mise à jour de l'otp
                    DB::table('users')->where('contact', $request->contact)->update(['code' => $verification_code]);

                    // Obtenir les infos de l'utilisateurs

                    $user = DB::table('users')->select('id')->where('contact', $request->contact)->first();

                    // Envoie de SMS
                    $otp =  DB::table('users')->where('contact', $request->contact)->pluck('code')->first();
                    $message = "Votre code  de connexion à MOOVPLAY est : $otp";

                    $now = date('Y-m-d H:i:s');

                    $mobile = '225' . $request->contact;

                    $message = [
                        "code_service" => "MOOVPLAY",
                        "password" => "zpkd8547@moovplay-2024",
                        "message" => $message,
                        "sender" => 'MOOVPLAY',
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


                    return response()->json([
                        'success' => true,
                        'message' => 'Veuillez vérifier votre messagerie pour terminer.',
                        'user' => $user,
                        'otp' => $otp
                    ], Response::HTTP_OK);
                }
            }
        }
        // }
    }



    public function login(Request $request)
    {

        //Validation
        $validator = Validator::make($request->all(), [
            'contact' => 'required',
            'code' => 'required',
        ], [
            'contact.required' => 'Le numéro de téléphone est requis.',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Veuillez entrer le code',
                    // 'message' => $validator->errors(),
                ]

            );
        }

        /* Validation Logic */
        $user = DB::table('users')
            ->where('contact', $request->contact)
            ->where('code', $request->code)
            ->first();


        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Votre OTP n'/est pas  valide.",

            ], Response::HTTP_OK);
        }

        $user = User::where('contact', $request->contact)->first();
        if ($user) {

            $token = Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Merci pour votre connexion.',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], Response::HTTP_OK);
        }
    }

    /**
     * Get the authenticated User.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }



    public function userProfile()
    {
        try {
            $user =  JWTAuth::parseToken()->authenticate();
            $Data['user'] = User::where('id', '=', $user->id)->first();

            $Data['questions'] = Question::where('status', 0)->get();

            return response()->json([
                'success' => true,
                'data' => $Data
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function compte()
    {
        try {

            // $user = auth()->user();
            $user =  JWTAuth::parseToken()->authenticate();

            // $all = Service::select('partenaires.x_user', 'partenaires.x_token', 'credential')->join("partenaires", 'services.partenaire_id', '=', 'partenaires.id')->get();

            // // $user = JWTAuth::parseToken()->authenticate();

            // $contact = $user->contact;

            // $numero = '225' . $contact;

            // $result = [];

            // foreach ($all as $item) {
            //     if (isset($item['credential']['url_consultation'])) {
            //         $result[] = [
            //             'x_user' => $item['x_user'],
            //             'x_token' => $item['x_token'],
            //             'url_consultation' => $item['credential']['url_consultation']
            //         ];
            //     }
            // }

            // $services_actifs = [];

            // foreach ($result as $item) {
            //     // Extraire les informations de l'élément actuel
            //     $url = $item['url_consultation'];
            //     $xuser = $item['x_user'];
            //     $xtoken = $item['x_token'];

            //     // Construire l'URL avec le numéro
            //     $url_with_numero = $url . '/' . $numero;

            //     // En-têtes de la requête cURL
            //     $headers = [
            //         'xuser:' . $xuser,
            //         'xtoken:' . $xtoken,
            //         'content-type: application/json'
            //     ];

            //     // Initialiser cURL
            //     $curl = curl_init();

            //     // Configuration de la requête cURL
            //     curl_setopt_array($curl, [
            //         CURLOPT_URL => $url_with_numero,
            //         CURLOPT_RETURNTRANSFER => true,
            //         CURLOPT_FOLLOWLOCATION => true, // Suivre les redirections
            //         CURLOPT_HTTPHEADER => $headers,
            //     ]);

            //     // Exécution de la requête cURL
            //     $response = curl_exec($curl);
            //     $error = curl_error($curl);

            //     // Vérification des erreurs
            //     if ($error) {
            //         echo "Erreur cURL : $error\n";
            //     } else {
            //         // Traitement de la réponse
            //         $response_array = json_decode($response, true);

            //         // Vérifier si le statusCode est égal à 0
            //         if (isset($response_array['statusCode']) && $response_array['statusCode'] === '0' && $response_array['status'] == 'actif') {
            //             // Récupérer le service_name
            //             $service_name = $response_array['service_name'];
            //             $transaction_id = isset($response_array['transaction_id']) ? $response_array['transaction_id'] : null;
            //             $date_fin_abonnement = isset($response_array['date_fin_abonnement']) ? $response_array['date_fin_abonnement'] : null;


            //             // Stocker le service_name dans le tableau des services actifs
            //             $services_actifs[] = $service_name;
            //         } elseif (isset($response_array['statusCode']) && ($response_array['statusCode'] === '1001' || $response_array['statusCode'] === '2061' || $response_array['statusCode'] === '3033')) {
            //             // Si le statusCode est 1001 ou 2061, ignorer cet élément et passer au suivant
            //             continue;
            //         }
            //     }

            //     // Fermeture de la session cURL
            //     curl_close($curl);
            // }

            // $getId = User::select('id')->where("contact", $contact)->value('id');

            // $abonne = Abonne::where('');

            // $services = Service::select('credential->service_name as service_name')->get();


            $data['services'] = DB::table('abonnes')
                ->join('services', 'abonnes.service_id', '=', 'services.id')
                ->where('abonnes.user_id', $user->id)
                ->whereNull('abonnes.date_desabonnement')
                ->get();



            $serviceIds = Abonne::select('service_id')->join("services", 'abonnes.service_id', '=', 'services.id')
                ->where('user_id', $user->id)
                ->where('date_desabonnement', '=', null)
                ->pluck('service_id');

            $data['recommandations'] = Service::limit(8)
                ->inRandomOrder()
                ->whereNotIn('id', $serviceIds)
                ->get();

            $data['favoris'] = Favori::join("services", 'favoris.service_id', '=', 'services.id')
                ->where('user_id', $user->id)
                ->get();


            return response()->json([
                'success' => true,
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expiré',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function desabonnement(Request $request)
    {
        $rules = [
            'transaction_id' => 'required',
        ];

        $customMessage = [
            'transaction_id.required' => 'Entrez le numéro de la transaction',
        ];

        $this->validate($request, $rules, $customMessage);
        try {
            $transactionId = $request->transaction_id;
            $serviceName = Transaction::select('nom_service')->where('transactionid', $transactionId)->value('nom_service');

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

            // Check if URL exists
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                $date =  date("Y-m-d");
                //  Mise à jour de la transaction
                Transaction::where('transactionid', $request->transaction_id)->update(['date_desabonnement' => $date, 'etat' => 'Desabonnement']);

                // Mise à jour de la table abonné

                Abonne::where('transactionid', $request->transaction_id)->update(['date_desabonnement' => date("Y-m-d")]);

                return response()->json([
                    'success' => true,
                    'message' => 'Vous êtes désabonnés de ce service.',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service est indisponible.',
                ], Response::HTTP_OK);
            }
            curl_close($ch);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur.',
            ], Response::HTTP_OK);
        }
    }

    public function updateProfil(Request $request)
    {
        $user =  JWTAuth::parseToken()->authenticate();
        User::where('id', $user->id)
            ->update([
                'name' => $request->name, 'firstname' => $request->firstname, 'email' => $request->email,
                'birthday' => $request->birthday, 'place_residence' => $request->place_residence, 'genre' => $request->genre
            ]);

        $info = User::where('id', $user->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Votre profil à été modifié avec succes.',
            'user' => $info,
        ], Response::HTTP_OK);
    }

    public function addFavorites(Request $request)
    {
        $rules = [
            'service_id' => 'required',
        ];

        $customMessage = [
            'service_id.required' => 'Entrez le numéro de la transaction',
        ];

        $this->validate($request, $rules, $customMessage);

        try {
            $user =  JWTAuth::parseToken()->authenticate();

            $verif = Favori::where('service_id', $request->service_id)->where('user_id', $user->id)->count();
            if ($verif == 0) {
                $favoris = new Favori();
                $favoris->service_id = $request->service_id;
                $favoris->user_id = $user->id;
                $favoris->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Vous avez ajouté ce service à vos favoris.',
                ], Response::HTTP_OK);
            }

            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà ce service en favoris.',
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur.',
            ], Response::HTTP_OK);
        }
    }

    public function addImage(Request $request)
    {

        $rules = [
            'file' => 'required',
            'name_file' => 'required',
        ];

        $customMessage = [
            'file.required' => 'Entrez l\'image',
            'name_file.required' => 'Entrez le nom de l\'image',
        ];

        $this->validate($request, $rules, $customMessage);
        try {
            $user =  JWTAuth::parseToken()->authenticate();

            $base64Image = $request->file;
            $imageData = base64_decode($base64Image);
            $filename = $request->name_file; // or any other desired image extension
            $directory = public_path('image/user_images/');
            file_put_contents($directory . $filename, $imageData);
            $imagePath = 'image/user_images/' . $filename;

            $users = User::where('id', $user->id)
                ->update(['image' => $request->name_file]);

            $info = User::where('id', $user->id)->first();


            return response()->json([
                'success' => true,
                'message' => 'Votre image de profil à été changé avec succes.',
                'user' => $info,
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur.',
            ], Response::HTTP_OK);
        }
    }

    public function Report(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
        ];

        $customMessage = [
            'title.required' => 'Entrez l\'image',
            'description.required' => 'Entrez le nom de l\'image',
        ];

        $this->validate($request, $rules, $customMessage);

        try {
            $user =  JWTAuth::parseToken()->authenticate();

            $reports = new Report();
            $reports->title = $request->title;
            $reports->description = $request->description;
            $reports->user_id = $user->id;
            $reports->save();

            return response()->json([
                'success' => true,
                'message' => 'Vous avez signalé un problème.',
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur.',
            ], Response::HTTP_OK);
        }
    }
}
