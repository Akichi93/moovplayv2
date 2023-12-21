<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_partenaire', 'email_partenaire', 'contact_partenaire','nom_correspondant'
    ];

    public function service() {
        return $this->hasMany(Service::class);
    }

}
