<?php

namespace App\Http\Controllers;

use App\Models\Abonne;
use App\Models\User;
use App\Models\Service;
use App\Models\Partenaire;
use Illuminate\Http\Request;
use DinhQuocHan\Twig\Extensions\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $service = Service::count();
        $partenaire = Partenaire::count();
        $user = User::count();
        $servicesWithAbonnesCount  = Service::select('nom_service as name')->withCount('abonnes as y')->get();

        $tbjson = json_encode($servicesWithAbonnesCount, true);

        return view('admin.home', compact('service', 'partenaire', 'user', 'servicesWithAbonnesCount', 'tbjson'));
    }
}
