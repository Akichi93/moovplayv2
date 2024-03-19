<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonne extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_service', 'service_name', 'amount','forfait','msisdn','forfait','transactionid','user_id','service_id','partenaire_id','date_fin_abonnement','date_abonnement'
    ];

    

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
