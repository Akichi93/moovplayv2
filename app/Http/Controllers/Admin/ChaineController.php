<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Chaine;

class ChaineController extends Controller
{
    public function chaines(){
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $chaines = Chaine::all();
        return view('admin.chaines.chaines')->with(compact('chaines', 'respo'));
    }

    public function addEditChaine(Request $request, $id = null){

        // dd($request->all());
        if ($id == "") {
            $titles = 'Ajouter une chaine';
            // ajout de fonctionnalités
            $chaine = new Chaine();
            $chainedata = array();

            $message = "La chaine a ete ajoutée avec succès !";
        } else {
            $titles = "Modifier la chaine";
            $chainedata = Chaine::where('id', $id)->first();
            $chainedata = json_decode(json_encode($chainedata), true);

            $chaine = Chaine::find($id);
            $message = "La chaine a été Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();

            
            $rules = [
                'title' => 'required',
                'image' => 'required',
                'link' => 'required',
                'link_complete' => 'required',
            ];

            $customMessage = [
                'title.required' => 'Le titre est requis',
                'image.required' => "Entrez l'image",
                'link.required' => "Entrez le lien de la vidéo",
                'link_complete.required' => "Entrez le lien de la vidéo",
            ];
            $this->validate($request, $rules, $customMessage);

            // $filename = null; // Initialize $filename with a default value
            if ($request->hasfile('image')) {
                $file = $request->file('image');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                $file->move('image/chaine_images/', $filename);

                $chaine->image = $filename;
            }
         

            $chaine->title = $data['title'];
            $chaine->link = $data['link'];
            $chaine->link_complete = $data['link_complete'];
            $chaine->description = $data['description'];
            $chaine->save();

            return redirect('/chaines');
        }
        return view('admin.chaines.add_edit_chaine')->with(compact('titles', 'chainedata'));
    }
}
