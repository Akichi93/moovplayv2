<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Partenaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PartenaireController extends Controller
{
    public function partenaires()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $partenaires = Partenaire::all();
        return view('admin.partenaires.partenaires')->with(compact('partenaires','respo'));
    }

    public function addEditPartenaire(Request $request, $id = null)
    {
        if ($id == "") {
            $title = 'Ajouter partenaire';
            // ajout de fonctionnalités
            $partenaire = new Partenaire();
            $partenairedata = array();

            $message = "La categorie a ete ajoutée avec succès !";
        } else {
            $title = "Modifier partenaire";
            $partenairedata = Partenaire::where('id', $id)->first();
            $partenairedata = json_decode(json_encode($partenairedata), true);

            $partenaire = Partenaire::find($id);
            $message = "Le partenaire à été Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'nom_partenaire' => 'required',
                'nom_correspondant' => 'required',
            ];

            $customMessage = [
                'nom_partenaire.required' => 'Le nom de la categorie est requis',
                'nom_correspondant.required' => 'Le nom du correspondant est requis',
            ];
            $this->validate($request, $rules, $customMessage);

            $partenaire->nom_partenaire = $data['nom_partenaire'];
            $partenaire->contact_partenaire = $data['contact_partenaire'];
            $partenaire->email_partenaire = $data['email_partenaire'];
            $partenaire->nom_correspondant = $data['nom_correspondant'];
            $partenaire->x_user = $data['x_user'];
            $partenaire->x_token = $data['x_token'];
            $partenaire->save();
            $request->session()->flash('success_message', $message);
            return redirect('/partenaires');
        }
        return view('admin.partenaires.add_edit_partenaire')->with(compact('title', 'partenairedata'));
    }

    public function desactivatepartenaire(Request $request,$id)
    {

        $partenaires = Partenaire::find($id);
        $partenaires->status = 1;
        $partenaires->save();

        $request->session()->flash('success_message', "Partenaire désactivé.");

        return back();
    }

    public function activatepartenaire(Request $request,$id)
    {
        $partenaires = Partenaire::find($id);
        $partenaires->status = 0;
        $partenaires->save();

        $request->session()->flash('success_message', "Partenaire activé.");

        return back();
    }
}
