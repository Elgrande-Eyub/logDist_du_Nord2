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
        Schema::create('avoirs_achats', function (Blueprint $table) {
            $table->id();

            $table->string('numero_avoirsAchat')->unique();

            $table->string('Exercice')->nullable();
            $table->string('Mois')->nullable();
            // $table->string('EtatPaiement')->nullable()->default('impaye'); // impaye - paye - en cours
            $table->string('Commentaire')->nullable();
            $table->dateTime('date_avoirs')->default(Carbon::now()->format('Y-m-d H:i:s'))->nullable();
            $table->boolean('Confirme')->default(false);
            $table->boolean('isLinked')->default(false);
            $table->float('Total_HT',8,2)->nullable();
            $table->integer('TVA')->nullable();
            $table->float('remise',8,2)->nullable();
            $table->float('Total_TVA',8,2)->nullable();
            $table->float('Total_TTC',8,2)->nullable();
            $table->string('attachement')->nullable();
     /*     $table->string('raison')->nullable();
            $table->integer('conditionPaiement')->nullable();
            $table->float('Total_Regler',8,2)->nullable()->default(0);
            $table->float('Total_Rester',8,2)->nullable();
 */
            $table->unsignedBigInteger('fournisseur_id')->nullable();
            $table->foreign('fournisseur_id')->nullable()->references('id')->on('fournisseurs')->onDelete('restrict');

            $table->unsignedBigInteger('bonretourAchat_id')->nullable();
            $table->foreign('bonretourAchat_id')->references('id')->on('bonretour_achats')->onDelete('restrict');

            $table->unsignedBigInteger('factureChange_id')->nullable();
            $table->foreign('factureChange_id')->references('id')->on('factures')->onDelete('restrict');

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
        Schema::dropIfExists('avoirs_achats');
    }
};
