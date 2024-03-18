<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbonnesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonnes', function (Blueprint $table) {
            $table->id();
            $table->string('nom_service');
            $table->string('service_name');
            $table->string('amount');
            $table->string('forfait');
            $table->string('msisdn');
            // $table->string('image');
            $table->string('transactionid');
            $table->foreignId('user_id');
            $table->foreignId('service_id')->nullable();
            $table->foreignId('partenaire_id');
            $table->dateTime('date_abonnement');
            $table->dateTime('date_fin_abonnement');
            $table->dateTime('date_desabonnement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abonnes');
    }
}
