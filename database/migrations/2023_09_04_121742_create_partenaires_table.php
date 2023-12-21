<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartenairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partenaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom_partenaire');
            $table->string('email_partenaire');
            $table->string('contact_partenaire')->nullable();
            $table->string('nom_correspondant')->nullable();
            $table->string('status')->default(0)->comment('0 = activé, 1 = desactivé');
            $table->string('x_user', 100);
            $table->string('x_token', 100);
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
        Schema::dropIfExists('partenaires');
    }
}
