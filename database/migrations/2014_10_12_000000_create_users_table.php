<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('date_naissance')->nullable();
            $table->string('lieu_residence')->nullable();
            $table->string('genre')->nullable();
            $table->string('image')->nullable();
            $table->string('code','8')->nullable();
            $table->dateTime('date_code')->nullable();
            $table->string('status')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
