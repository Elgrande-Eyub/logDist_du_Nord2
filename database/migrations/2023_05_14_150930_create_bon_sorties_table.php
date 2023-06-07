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
        Schema::create('bon_sorties', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();
            $table->dateTime('dateSortie')->default(Carbon::now());
            $table->boolean('Confirme')->default(false);
            $table->float('camionKM',7,2); // default camion kelometrage dik lwe9t
            $table->string('Commentaire')->nullable();
            $table->unsignedBigInteger('vendeur_id');
            $table->foreign('vendeur_id')->references('id')->on('vendeurs')->onDelete('restrict');

            $table->unsignedBigInteger('aideVendeur_id')->nullable();
            $table->foreign('aideVendeur_id')->references('id')->on('vendeurs')->onDelete('restrict');

            $table->unsignedBigInteger('aideVendeur2_id')->nullable();
            $table->foreign('aideVendeur2_id')->references('id')->on('vendeurs')->onDelete('restrict');

            $table->unsignedBigInteger('camion_id');
            $table->foreign('camion_id')->references('id')->on('camions')->onDelete('restrict');


            $table->unsignedBigInteger('secteur_id');
            $table->foreign('secteur_id')->references('id')->on('secteurs')->onDelete('restrict');

            $table->unsignedBigInteger('warehouse_id');
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
        Schema::dropIfExists('bon_sorties');
    }
};
