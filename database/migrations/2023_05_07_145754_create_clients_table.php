<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('nom_Client');
            $table->string('code_Client');
            $table->string('CIN_Client')->nullable();
            $table->string('ICE_Client')->nullable();
            $table->string('RC_Client')->nullable();
            $table->string('telephone_Client');
            $table->string('email_Client')->nullable();
            $table->string('adresse_Client');
            $table->string('Pattent_Client');

            $table->softDeletes();
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
        Schema::dropIfExists('clients');
    }
};
