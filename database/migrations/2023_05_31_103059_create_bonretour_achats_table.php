<?php

use Carbon\Carbon;
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
        Schema::create('bonretour_achats', function (Blueprint $table) {
            $table->id();

            $table->string('Numero_bonRetour')->unique();
            $table->string('Exercice')->nullable();
            $table->string('Mois')->nullable();
            $table->string('Etat')->nullable();
            $table->string('Commentaire')->nullable();
            $table->string('raison')->nullable();
            $table->dateTime('date_BRetour')->default(Carbon::now()->format('Y-m-d H:i:s'))->nullable();
            $table->boolean('Confirme')->default(false)->nullable();
            $table->float('Total_HT',8,2)->nullable();
            $table->float('Total_TVA',8,2)->nullable();
            $table->float('Total_TTC',8,2)->nullable();
            $table->float('remise',8,2)->nullable();
            $table->integer('TVA')->nullable();

            $table->unsignedBigInteger('fournisseur_id');
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('restrict');

            $table->unsignedBigInteger('bonLivraison_id')->nullable();
            $table->foreign('bonLivraison_id')->references('id')->on('bon_livraisons')->onDelete('restrict');

            $table->unsignedBigInteger('bonLivraisonChange_id')->nullable();
            $table->foreign('bonLivraisonChange_id')->references('id')->on('bon_livraisons')->onDelete('restrict');

            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonretour_achats');
    }
};
