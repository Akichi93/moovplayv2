<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('nom_service');
            $table->foreignId('categorie_id');
            $table->foreignId('partenaire_id')->nullable();

            $table->string('status')->default(0)->comment('0 = activé, 1 = desactivé');

            $table->foreign('categorie_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->foreign('partenaire_id')
                ->references('id')
                ->on('partenaires')
                ->onDelete('cascade');

            $table->longText('description')->nullable();
            $table->string('code_souscription')->nullable();
            $table->string('code_desouscription')->nullable();
            $table->json('credential');

            $table->string('image')->nullable();

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
        Schema::dropIfExists('services');
    }
}
