<?php

namespace App\Http\Controllers\Front;

use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('JWT', ['except' => ['login', 'register']]);
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
                    $user->save();

                    $lastID = DB::table('users')->max('id');

                    $Dernier = $user->id;

                    $contact = $request->all();

                    $otp =  DB::table('users')->where('contact', $request->contact)->pluck('code')->first();
                    $message = "Votre code de connexion à MOOVPLAY est : $otp";
                    // $now = date('Y-m-d H:i:s');

                    // $mobile = '225' . $request->contact;

                    // $message = [
                    //     "code_service" => "MOOVPLAY",
                    //     "password" => "zpkd8547@moovplay-2024",
                    //     "message" => $message,
                    //     "sender" => 'MOOVPLAY',
                    //     "msisdn" => $mobile,
                    //     "datetime" => $now,
                    // ];

                    // $json = json_encode($message);

                    // $ch = curl_init();
                    // curl_setopt($ch, CURLOPT_URL, "https://smartdev.ci/sms/smsMT.php");
                    // curl_setopt($ch, CURLOPT_POST, 1);

                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // $server_output = curl_exec($ch);
                    // curl_close($ch);

                    // Log::info('Demande :', ['data' => $server_output]);



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

                    // $now = date('Y-m-d H:i:s');

                    // $mobile = '225' . $request->contact;

                    // $message = [
                    //     "code_service" => "MOOVPLAY",
                    //     "password" => "zpkd8547@moovplay-2024",
                    //     "message" => $message,
                    //     "sender" => 'MOOVPLAY',
                    //     "msisdn" => $mobile,
                    //     "datetime" => $now,
                    // ];

                    // $json = json_encode($message);

                    // $ch = curl_init();
                    // curl_setopt($ch, CURLOPT_URL, "https://smartdev.ci/sms/smsMT.php");
                    // curl_setopt($ch, CURLOPT_POST, 1);

                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // $server_output = curl_exec($ch);
                    // curl_close($ch);

                    // Log::info('Demande :', ['data' => $server_output]);


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
        $data = $request->all();

        $rules = [
            'contact' => 'required',
            'code' => 'required',
        ];

        $customMessage = [
            'contact.required' => 'contact est requis',
            'code.required' => 'l\'otp est requis',
        ];
        $this->validate($request, $rules, $customMessage);


        // Trouver l'utilisateur correspondant au contact et au code
        $user = User::where('contact', $request->contact)->where('code', $request->code)->first();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Les informations de connexion sont incorrectes.",
            ], Response::HTTP_UNAUTHORIZED);

        } 
        
        $user = User::where('contact', $request->contact)->first();
        if ($user) {

            $token = JWTAuth::fromUser($user);

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
    // public function refresh()
    // {
    //     return $this->respondWithToken(auth()->refresh());
    // }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // protected function respondWithToken($token)
    // {
    //     return response()->json([
    //         'success' => true,
    //         'data' =>
    //         [
    //             'access_token' => $token,
    //             'admin_id' =>  auth('admin')->user()->id,
    //             'name' =>  auth('admin')->user()->nom_complet,
    //             'email' =>  auth('admin')->user()->email,
    //             'token_type' => 'bearer',
    //             'expires_in' => auth('admin')->factory()->getTTL() * 10000000,
    //             'role' => auth('admin')->user()->role_id,
    //             'gravatars' => auth('admin')->user()->gravatars,
    //         ]
    //     ]);
    // }
}
