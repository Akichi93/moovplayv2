<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function postLogin(Request $request){
        // if ($request->isMethod('post')) {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required|min:6'
            ]);  
        
            if (Auth::guard('admin')->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {

                
                return redirect()->intended('/home');
            }

            return back();
        
        // }
        // return view('auth.login');
    }

    public function admins()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $admins = Admin::all();
        return view('admin.users.users')->with(compact('admins','respo'));
    }

    public function addEditAdmin(Request $request, $id = null)
    {
        if ($id == "") {
            $title = 'Ajouter administrateur';
            // ajout de fonctionnalités
            $Admin = new Admin();
            $admindata = array();
            $getAdmin = array();
            
            $message = "L'administrateur a ete ajoutée avec succès !";
        } else {
            $title = "Modifier administrateur";
            $admindata = Admin::where('id', $id)->first();
            $admindata = json_decode(json_encode($admindata), true);
          
            $category = Admin::find($id);
            $message = "L'administrateur a ete Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'name' => 'required|max:30',
                'prenom' => 'required',
                'email' => 'required|unique:users|email|max:255',
                'role' => 'required',
                'password' => 'required|between:8,255|confirmed',
                'password_confirmation' => 'required'
            ];
    
            $customMessages = [
                'name.required' => 'Veuillez entrer le nom de l\'utilisateur',
                'name.max' => 'Le nom d\'utilisateur ne dois pas depasser 30 caractères',
                'prenom.required' => 'Veuillez entrer le prenom de l\'utilisateur',
                'role.required' => 'Veuillez entrer le prenom de l\'utilisateur',
                'email.required' => 'Veuillez entre l\'email ',
                'password.required' => 'Veuillez entrez un mot de passe ',
                'password.min' => 'Veuillez entrez un mot de passe d\'au moins 8 caractères',
                'password.regex' => 'Le mot de passe doit contenir des majuscules miniscules et symboles',
                'password_confirmation.required' => 'La confirmation du mot de passe est requis',
            ];
    
            $this->validate($request, $rules, $customMessages);

                $Admin->name = $data['name'];
                $Admin->prenom = $data['prenom'];
                $Admin->role = $data['role'];
                $Admin->email = $data['email'];
                $Admin->password = Hash::make($request->password);
                $Admin->save();
                // Session::flash('success_message', $message);
                return redirect('/admins'); 
           

            
        }
        return view('admin.users.add_edit_user')->with(compact('title', 'admindata'));
    }
}
