<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('num_transaction')->unique();
            $table->dateTime('date_transaction')->default(Carbon::now()->format('Y-m-d H:i:s'));
            $table->string('num_virement')->nullable()->unique();;
            $table->float('montant',12,2);
            $table->string('commentaire')->nullable();
            $table->string('modePaiement')->nullable(); // escpece , cheque , banqueTransaction , virement
            $table->string('numero_cheque')->nullable();
            $table->date('delais_cheque')->nullable();
            $table->string('etat_cheque')->nullable();

            $table->unsignedBigInteger('factureAchat_id')->nullable()->default(null);
            $table->foreign('factureAchat_id')->references('id')->on('factures')->onDelete('restrict');

            $table->unsignedBigInteger('factureVente_id')->nullable()->default(null);
            $table->foreign('factureVente_id')->references('id')->on('facture_ventes')->onDelete('restrict');

            $table->unsignedBigInteger('paiementDepense_id')->nullable()->default(null);
            $table->foreign('paiementDepense_id')->references('id')->on('paiement_depenses')->onDelete('restrict');

            $table->unsignedBigInteger('venteSecteur_id')->nullable()->default(null);
            $table->foreign('VenteSecteur_id')->references('id')->on('vente_secteurs')->onDelete('restrict');

            $table->unsignedBigInteger('Credit_id')->nullable()->default(null);
            $table->foreign('Credit_id')->references('id')->on('credits')->onDelete('restrict');

            /* $table->unsignedBigInteger('avoirsAchat_id')->nullable()->default(null);
            $table->foreign('avoirsAchat_id')->references('id')->on('avoirs_achats')->onDelete('restrict');

            $table->unsignedBigInteger('avoirsVente_id')->nullable()->default(null);
            $table->foreign('avoirsVente_id')->references('id')->on('avoirs_ventes')->onDelete('restrict');
            */

            $table->unsignedBigInteger('journal_id')->nullable();
            $table->foreign('journal_id')->references('id')->on('journals')->onDelete('restrict');


            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
