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
        Schema::create('bon_consignations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('facture_id')->nullable();
            $table->foreign('facture_id')->references('id')->on('factures')->onDelete('restrict');

            $table->string('numero_bonConsignation');

            $table->string('representant')->nullable();
            $table->string('transporteur')->nullable();
            $table->string('matriculeCamion')->nullable();
            $table->integer('conditionPaiement')->nullable();

            $table->float('Total_Emballages',8,2);
            $table->string('etat')->default(false); // retourne , paye , dans l'entrepot
            $table->string('attachement')->nullable(); // bon Image

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
        Schema::dropIfExists('bon_consignations');
    }
};
