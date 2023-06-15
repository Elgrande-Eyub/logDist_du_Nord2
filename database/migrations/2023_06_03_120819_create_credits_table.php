<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->id();

            $table->string('EtatPaiement')->nullable()->default('impaye'); // impaye - Regle - en cours
            $table->string('Commentaire')->nullable();
            $table->boolean('Confirme')->default(false)->nullable();
            $table->float('montant',8,2);
            $table->float('Total_Regler',8,2)->nullable()->default(0);
            $table->float('Total_Rester',8,2)->nullable();

            $table->unsignedBigInteger('fournisseur_id')->nullable();
            $table->foreign('fournisseur_id')->nullable()->references('id')->on('fournisseurs')->onDelete('restrict');

            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->nullable()->references('id')->on('clients')->onDelete('restrict');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credits');
    }
};
