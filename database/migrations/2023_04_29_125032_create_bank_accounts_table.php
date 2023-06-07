<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('nomBank');
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('numero_compt');
            $table->string('rib_compt');
            $table->float('solde',16,2)->nullable();
            $table->string('Commentaire')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_accounts');
    }
};
