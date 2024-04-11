<?php

namespace App\Providers;

use App\Models\Categorie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // $menus = Categorie::where('status','0')->get();

        // // dd($connect);

        // Paginator::useBootstrap();

        // view()->share(['menus' => $menus]);

        if (env('APP_ENV') !== 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
