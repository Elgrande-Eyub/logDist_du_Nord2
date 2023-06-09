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
        Schema::create('vente_secteurs', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique(); // the same as the linked bon Sortie

            $table->dateTime('dateEntree')->default(Carbon::now());
            $table->string('EtatPaiement')->nullable()->default('impaye');
            $table->bigInteger('kilometrageFait');
            $table->boolean('Confirme')->default(false);
            $table->float('Total_HT',8,2)->nullable();
            $table->integer('TVA')->nullable();
            $table->float('Total_TVA',8,2)->nullable();
            $table->float('Total_TTC',8,2)->nullable();

            $table->unsignedBigInteger('bonSortie_id');
            $table->foreign('bonSortie_id')->references('id')->on('bon_sorties')->onDelete('cascade');

            $table->float('Total_Regler',8,2)->nullable()->default(0);
            $table->float('Total_Rester',8,2)->nullable();

            $table->unsignedBigInteger('vendeur_id');
            $table->foreign('vendeur_id')->references('id')->on('vendeurs')->onDelete('cascade');

            $table->unsignedBigInteger('aideVendeur_id')->nullable();
            $table->foreign('aideVendeur_id')->references('id')->on('vendeurs')->onDelete('cascade');

            $table->unsignedBigInteger('aideVendeur2_id')->nullable();
            $table->foreign('aideVendeur2_id')->references('id')->on('vendeurs')->onDelete('cascade');

            $table->unsignedBigInteger('camion_id');
            $table->foreign('camion_id')->references('id')->on('camions')->onDelete('cascade');

            $table->unsignedBigInteger('secteur_id');
            $table->foreign('secteur_id')->references('id')->on('secteurs')->onDelete('cascade');

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');



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
        Schema::dropIfExists('vente_secteurs');
    }
};
