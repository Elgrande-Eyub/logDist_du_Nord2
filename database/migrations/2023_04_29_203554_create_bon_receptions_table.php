<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bon_receptions', function (Blueprint $table) {
            $table->id();

            $table->string('Numero_bonReception')->unique();
            $table->string('Exercice')->nullable();
            $table->string('Mois')->nullable();
            $table->string('Etat')->nullable();
            $table->string('Commentaire')->nullable();
            $table->dateTime('date_BReception')->default(Carbon::now()->format('Y-m-d H:i:s'))->nullable();
            $table->boolean('Confirme')->default(false)->nullable();
            $table->float('Total_HT',8,2)->nullable();
            $table->float('Total_TVA',8,2)->nullable();
            $table->float('Total_TTC',8,2)->nullable();


            $table->unsignedBigInteger('fournisseur_id')->nullable();
            $table->foreign('fournisseur_id')->nullable()->references('id')->on('fournisseurs')->onDelete('restrict');

            $table->unsignedBigInteger('bonCommande_id')->nullable();
            $table->foreign('bonCommande_id')->references('id')->on('bon_commandes')->onDelete('restrict');

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
        Schema::dropIfExists('bon_receptions');
    }
};
