<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    public function slides()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $slides = Banner::all();
        return view('admin.slides.slides')->with(compact('slides', 'respo'));
    }

    public function addEditSlide(Request $request, $id = null)
    {

        if ($id == "") {
            $titles = 'Ajouter slide';
            // ajout de fonctionnalités
            $slide = new Banner();
            $slidedata = array();
            // $getslides = array();

            $message = "Le slide a ete ajoutée avec succès !";
        } else {
            $titles = "Modifier slide";
            $slidedata = Banner::where('id', $id)->first();
            $slidedata = json_decode(json_encode($slidedata), true);

            $slide = Banner::find($id);
            $message = "Le slide a ete Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($request->all());

            // dd($data);
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'type' => 'required',
            ];

            $customMessage = [
                'title.required' => 'Le titre est requis',
                'description.required' => "Entrez l'image",
                'type.required' => "Entrez l'image",
            ];
            $this->validate($request, $rules, $customMessage);

            // $filename = null; // Initialize $filename with a default value
            if ($request->hasfile('image')) {
                $file = $request->file('image');
                $extenstion = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extenstion;
                $file->move('image/banner_images/', $filename);

                $slide->image = $filename;
            }
            // elseif (isset($slidedata['image']) && $slidedata['image'] != null) {
            //     // Si aucun fichier n'est téléchargé, conserver l'ancienne donnée
            //     $newFileName = $slidedata['image']; // Utilisation de l'ancien nom de fichier
            // } 
            // else {
            //     $newFileName = null;
            // }

            $slide->title = $request->get('title');
            $slide->alt = $data['title'];
            $slide->link = $data['link'];
            
            $slide->type = $data['type'];
            $slide->description = $data['description'];
            $slide->save();

            return redirect('/slides');
        }
        return view('admin.slides.add_edit_slide')->with(compact('titles', 'slidedata'));
    }
}
