<?php

namespace App\Http\Controllers\Admin;

use Image;
use App\Models\Admin;
use App\Models\Offre;
use App\Models\Service;
use App\Models\Categorie;
use App\Models\Partenaire;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function services()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $services = Service::with(['partenaires', 'categories'])->get();

        return view('admin.services.services')->with(compact('services', 'respo'));
    }

    public function addEditService(Request $request, $id = null)
    {
        if ($id == "") {
            $title = 'Ajouter service';
            // ajout de fonctionnalités
            $service = new Service();
            $servicedata = array();

            $getPartenaires = Partenaire::all();
            // $getPartenaires = json_decode(json_encode($getPartenaires), true);

            $getCategories = Categorie::all();
            // $getCategories = json_decode(json_encode($getCategories), true);

            $message = "Le service a ete ajoutée avec succès !";
        } else {
            $title = "Modifier partenaire";
            $servicedata = Service::where('id', $id)->first();
            $servicedata = json_decode(json_encode($servicedata), true);

            $getPartenaires = Partenaire::all();
            // $getPartenaires = json_decode(json_encode($getPartenaires), true);

            $getCategories = Categorie::all();
            // $getCategories = json_decode(json_encode($getCategories), true);


            $service = Service::find($id);
            $message = "Le service a ete Modifée avec succès !";
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'nom_service' => 'required',
                'categorie_id' => 'required',
                'partenaire_id' => 'required',
                // 'file' => 'required',
            ];

            $customMessage = [
                'nom_service.required' => 'Le nom du service',
                'categorie_id.required' => 'Selectionnez une catégorie',
                'partenaire_id.required' => 'Selectionnez un partenaire',
                // 'file.required' => 'Selectionnez une image',
            ];
            $this->validate($request, $rules, $customMessage);

            if ($request->file != null) {

                foreach ($request->file('file') as $file) {
                    $filename = $file->getClientOriginalName();
                    $file->move('image/service_images', $filename);

                    $insert[] = "$filename";
                }
            } else {
                $oldImages = json_decode($servicedata['image'], true); // Récupérer les anciens noms de fichiers
                $insert = $oldImages ?: [];

                    $insert[] = $filename;
                }

                $service->image = $insert;
            } 


            if ($request->icone != null) {

                $fileNameWithTheExtension = request('icone')->getClientOriginalName();

                //obtenir le nom de l'image

                $iconeName = pathinfo($fileNameWithTheExtension, PATHINFO_FILENAME);

                // extension
                $extension = request('icone')->getClientOriginalExtension();

                // creation de nouveau nom
                $newIconeName = $iconeName . '_' . time() . '.' . $extension;

                $path = request('icone')->move('image/service_images', $newIconeName);

            } else {
                // Si aucun fichier n'est téléchargé, conserver l'ancienne donnée
                $newIconeName = $servicedata['icone']; // Utilisation de l'ancien nom de fichier

                $service->icone = $newIconeName;
            }





            // Création d'object
            $periode = $data['periode'];
            $tarif = $data['tarif'];
            $avantage = $data['avantage'];

            $array = [];
            for ($i = 0; $i < count($periode); $i++) {
                $object = [
                    "periode" => $periode[$i],
                    "tarif" => $tarif[$i],
                    "description" => $avantage[$i],
                ];

                array_push($array, $object);
            }


            $service->categorie_id = $data['categorie_id'];
            $service->partenaire_id = $data['partenaire_id'];
            $service->description = $data['description'];
            $service->code_souscription = $data['code_souscription'];
            $service->code_desouscription = $data['code_desouscription'];
            $service->nom_service = $data['nom_service'];
            $service->link = $data['link'];

            $service->icone = $newIconeName;
            $service->image = json_encode($insert);
            $service->service_url = Str::slug($request->input('nom_service'), "-");
            $service->credential = [
                'service_name' => $request->service_name,
                'url_demande_abonnement' => $request->url_demande_abonnement,
                'url_desabonnement' => $request->url_desabonnement,
                'url_confirmation_abonnement' => $request->url_confirmation_abonnement,
                'url_consultation' => $request->url_consultation,
                'bundle' =>  $array
            ];
            $service->ressource = [];
            $service->save();

            $request->session()->flash('success_message', $message);
            return redirect('/services');
        
    
        return view('admin.services.add_edit_service')->with(compact('title', 'servicedata', 'getPartenaires', 'getCategories'));
    }

    public function addOffres(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            foreach ($data['periode'] as $key => $value) {
                if (!empty($value)) {
                    $attrCountSKU = Offre::where('service_id', $id)->where('periode', $value)->count();
                    if ($attrCountSKU > 0) {
                        $message = 'Periode existe deja. SVP veuillez ajouter un autre';
                        // Session::flash('error_message', $message);
                        return redirect()->back();
                    }

                    $attribute = new Offre();
                    $attribute->service_id = $id;
                    $attribute->periode = $value;
                    $attribute->tarifs = $data['tarif'][$key];
                    $attribute->avantages = $data['avantage'][$key];
                    $attribute->save();
                }
            }

            $success_message = 'L\'attribut du produit a été ajouté avec sucess';
            // Session::flash('success_message', $success_message);
            return redirect()->back();
        }

        $servicedata = Service::with(['offres', 'partenaires', 'categories'])->find($id);
        $servicedata = json_decode(json_encode($servicedata), true);


        $title = "Ajout d'offre ";

        return view('admin.services.add_offre')->with(compact('servicedata', 'title'));
    }

    public function editOffres(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            foreach ($data['attrId'] as $key => $attr) {
                if (!empty($attr)) {
                    Offre::where(['id' => $data['attrId'][$key]])
                        ->update(['periode' => $data['periode'][$key], 'tarifs' => $data['tarif'][$key], 'avantages' => $data['avantage'][$key]]);
                }
            }

            // $success_message = 'L\'attribut du produit a été modifié avec sucess';
            // Session::flash('success_message', $success_message);
            return redirect()->back();
        }
    }

    public function desactivateservice(Request $request, $id)
    {

        $services = Service::find($id);
        $services->status = 1;
        $services->save();

        $request->session()->flash('success', "Service désactivé.");

        return back();
    }

    public function activateservice(Request $request, $id)
    {
        $services = Service::find($id);
        $services->status = 0;
        $services->save();

        $request->session()->flash('success', "Service activé.");

        return back();
    }
}
