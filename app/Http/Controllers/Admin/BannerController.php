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

            // dd($data);
            $rules = [
                'title' => 'required',
                'image' => 'required',
                'type' => 'required',
            ];

            $customMessage = [
                'title.required' => 'Le titre est requis',
                'image.required' => "Entrez l'image",
                'type.required' => "Entrez l'image",
            ];
            $this->validate($request, $rules, $customMessage);


            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileNameWithTheExtension = $file->getClientOriginalName();
                //obtenir le nom de l'image

                $fileName = pathinfo($fileNameWithTheExtension, PATHINFO_FILENAME);

                // extension
                $extension = $file->getClientOriginalExtension();

                // creation de nouveau nom
                $newFileName = $fileName . '_' . time() . '.' . $extension;

                $path = $file->move('image/banner_images', $newFileName);
            } else {
                // Si aucun fichier n'est téléchargé, conserver l'ancienne donnée
                $newFileName = $slidedata['image']; // Utilisation de l'ancien nom de fichier
            }

            $slide->title = $request->get('title');
            $slide->alt = $data['title'];
            $slide->link = $data['link'];
            $slide->image = $newFileName;
            $slide->type = $data['type'];
            $slide->save();

            return redirect('/slides');
        }
        return view('admin.slides.add_edit_slide')->with(compact('titles', 'slidedata'));
    }
}
