<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'order_id',
        'user_id',
        'service_id',
        'partenaire_id',
        'nom_service',
        'forfait',
        'amount',
        'msisdn',
        'service_name',
        'order_url',
        'contact',
        'transactionid',
        'canal',
        'mod_paiement',
    ];

        // Cast credential JSON to array
        protected $casts = [
            'image' => 'array',
            'ressources' => 'array',
        ];
    
}
