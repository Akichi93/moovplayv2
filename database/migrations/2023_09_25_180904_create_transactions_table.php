<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nom_service');
            $table->string('service_name');
            $table->string('amount');
            $table->string('forfait');
            $table->string('msisdn');
            $table->string('order_id');
            $table->string('nom_partenaire');
            $table->date('date_transaction');
            $table->string('order_url')->nullable();

            $table->foreignId('user_id');
            $table->foreignId('service_id')->nullable();
            $table->foreignId('partenaire_id');

            $table->string('status')->default("Pending");

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->foreign('partenaire_id')
                ->references('id')
                ->on('partenaires')
                ->onDelete('cascade');

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
        Schema::dropIfExists('transactions');
    }
}
