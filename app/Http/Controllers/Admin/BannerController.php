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
        return view('admin.slides.slides')->with(compact('slides','respo'));
    }

    public function addEditSlide(Request $request, $id = null)
    {
        if ($id == "") {
            $title = 'Ajouter slide';
            // ajout de fonctionnalités
            $slide = new Banner();
            $slidedata = array();
            $getslides = array();
            
            $message = "Le slide a ete ajoutée avec succès !";
        } else {
            $title = "Modifier slide";
            $slidedata = Banner::where('id', $id)->first();
            $slidedata = json_decode(json_encode($slidedata), true);
          
            $category = Banner::find($id);
            $message = "Le slide a ete Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
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

            $category->title = $data['title'];
            $category->alt = $data['title'];
            $category->link = $data['link'];
            $category->image = $data['image'];
            $category->type = $data['type'];
            $category->save();
        
            return redirect('/slides');
        }
        return view('admin.slides.add_edit_slide')->with(compact('title', 'slidedata'));
    }
}
