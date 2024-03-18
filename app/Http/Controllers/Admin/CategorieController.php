<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Categorie;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class CategorieController extends Controller
{
    public function categories()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $categories = Categorie::all();
        return view('admin.categories.categories')->with(compact('categories','respo'));
    }

    public function addEditCategorie(Request $request, $id = null)
    {
        if ($id == "") {
            $title = 'Ajouter Categorie';
            // ajout de fonctionnalités
            $category = new Categorie();
            $categorydata = array();
            $getCategories = array();
            
            $message = "La categorie a ete ajoutée avec succès !";
        } else {
            $title = "Modifier categorie";
            $categorydata = Categorie::where('id', $id)->first();
            $categorydata = json_decode(json_encode($categorydata), true);
          
            $category = Categorie::find($id);
            $message = "La categorie a ete Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'nom_categorie' => 'required',
                'position' => 'required',
            ];

            $customMessage = [
                'nom_categorie.required' => 'Le nom de la categorie',
                'position.required' => "Entrez l'url de la catégorie",
            ];
            $this->validate($request, $rules, $customMessage);

            // verification 
            $verif = Categorie::where('status',0)->count();

            // if($verif >= 4){
            //     return redirect('/categories'); 
            // }else{
                $category->nom_categorie = $data['nom_categorie'];
                $category->url = Str::slug($request->input('nom_categorie'), "-");
                $category->position = $data['position'];
                $category->save();

                $request->session()->flash('success_message', $message);
            
                return redirect('/categories'); 
            // }

            
        }
        return view('admin.categories.add_edit_categorie')->with(compact('title', 'categorydata'));
    }
}
