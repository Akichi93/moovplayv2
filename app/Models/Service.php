<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_service', 'partenaire_id', 'categorie_id', 'status', 'code_souscription', 'code_desouscription','service_url'
    ];

    // Cast credential JSON to array
    protected $casts = [
        'credential' => 'array',
        'image' => 'array',
        'ressources' => 'array',
    ];

    public function partenaires()
    {
        return $this->belongsTo(Partenaire::class, 'partenaire_id');
    }

    public function categories()
    {
        return $this->belongsTo(Categorie::class,'categorie_id');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }

    public function abonnes()
    {
        return $this->hasMany(Abonne::class);
    }

}
