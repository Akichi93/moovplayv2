<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_categorie', 'url'
    ];

    public function service()
    {
        return $this->hasMany(Service::class);
    }

    public static function catDetails($url)
    {
        $catDetails = Categorie::select('id', 'nom_categorie', 'url')->where('url', $url)->first()->toArray();

        // $parentCategory = Categorie::select('nom_categorie', 'url')->first()->toArray();
        $breadcrumb = '<a href="' . url($catDetails['url']) . '">' . $catDetails['nom_categorie'] . '</a>&nbsp;&nbsp;<a href="' . url($catDetails['url']) . '">' . $catDetails['nom_categorie'] . '</a>';

        $catIds = array();
        $catIds[] = $catDetails['id'];
        // foreach ($catDetails as  $subcat) {
        //     $catIds[] = $subcat['id'];
        // }
        return array('catIds' => $catIds, 'catDetails' => $catDetails, 'breadcrumb' => $breadcrumb);
    }
}
