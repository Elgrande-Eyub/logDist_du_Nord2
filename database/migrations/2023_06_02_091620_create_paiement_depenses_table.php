<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('paiement_depenses', function (Blueprint $table) {
            $table->id();

            $table->string('numero_Depense')->unique()->nullable();
            $table->string('EtatPaiement')->nullable()->default('impaye'); // impaye - paye - en cours
            $table->string('Commentaire')->nullable();
            $table->dateTime('dateDepense')->default(Carbon::now()->format('Y-m-d H:i:s'))->nullable();
            $table->boolean('Confirme')->default(false)->nullable();
            $table->float('montantTotal',8,2)->nullable();
            $table->integer('TVA')->nullable();
            $table->float('remise',8,2)->nullable();
            $table->float('Total_Regler',8,2)->nullable()->default(0);
            $table->float('Total_Rester',8,2)->nullable();
            $table->string('attachement')->nullable();

            $table->unsignedBigInteger('depense_id')->nullable();
            $table->foreign('depense_id')->references('id')->on('depenses')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down()
    {
        Schema::dropIfExists('paiement_depenses');
    }
};
