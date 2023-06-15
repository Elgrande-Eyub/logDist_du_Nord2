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
        Schema::create('bon_livraisons', function (Blueprint $table) {
            $table->id();

            $table->string('Numero_bonLivraison')->unique();
            $table->string('Exercice')->nullable();
            $table->string('Mois')->nullable();
            $table->string('Etat')->nullable();
            $table->string('Commentaire')->nullable();
            $table->dateTime('date_Blivraison')->default(Carbon::now()->format('Y-m-d H:i:s'))->nullable();
            $table->boolean('Confirme')->default(false)->nullable();
            $table->float('Total_HT',8,2)->nullable();
            $table->float('Total_TVA',8,2)->nullable();
            $table->float('Total_TTC',8,2)->nullable();
            $table->float('remise',8,2)->nullable();
            $table->integer('TVA')->nullable();
            $table->string('attachement')->nullable();

            $table->unsignedBigInteger('fournisseur_id');
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('restrict');

            $table->unsignedBigInteger('bonCommande_id')->nullable();
            $table->foreign('bonCommande_id')->references('id')->on('bon_commandes')->onDelete('restrict');

            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');

            $table->unsignedBigInteger('camion_id')->nullable();
            $table->foreign('camion_id')->references('id')->on('camions')->onDelete('restrict');

            $table->unsignedBigInteger('transporteur_id')->nullable();
            $table->foreign('transporteur_id')->references('id')->on('employees')->onDelete('restrict')->onUpdate('restrict');


            // for the Bon retour Case
            $table->boolean('isChange')->default(false);

            // $table->unsignedBigInteger('bonretourAchat_id')->nullable();
            // $table->foreign('bonretourAchat_id')->references('id')->on('bonretour_achats')->onDelete('restrict');
            // $table->foreignId('bonretourAchat_id')->constrained('bonretour_achats');

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
        Schema::dropIfExists('bon_livraisons');
    }
};
