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
        Schema::create('bon_commande_ventes', function (Blueprint $table) {
            $table->id();


            $table->string('Numero_bonCommandeVente')->unique();
            $table->string('Exercice')->nullable();
            $table->string('Mois')->nullable();
            $table->string('Etat')->nullable();
            $table->string('Commentaire')->nullable();
            $table->dateTime('date_BCommandeVente')->default(Carbon::now()->format('Y-m-d H:i:s'))->nullable();
            $table->boolean('Confirme')->default(false)->nullable();
            $table->float('Total_HT',8,2)->nullable();
            $table->float('remise',8,2)->nullable();
            $table->integer('TVA')->nullable();
            $table->float('Total_TVA',8,2)->nullable();
            $table->float('Total_TTC',8,2)->nullable();
            $table->string('attachement')->nullable();

            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->nullable()->references('id')->on('clients')->onDelete('restrict');

            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('bon_commande_ventes');
    }
};
