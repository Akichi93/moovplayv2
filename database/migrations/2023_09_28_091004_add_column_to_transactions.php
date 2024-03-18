<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('image')->after('status')->nullable();
            $table->string('transactionid')->after('image')->nullable();
            $table->string('xuser')->after('transactionid')->nullable();
            $table->string('xtoken')->after('xuser')->nullable();
            $table->dateTime('date_fin_abonnement')->after('xtoken')->nullable();
            $table->dateTime('date_desabonnement')->after('date_fin_abonnement')->nullable();
            $table->string('etat')->after('date_desabonnement')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
}
