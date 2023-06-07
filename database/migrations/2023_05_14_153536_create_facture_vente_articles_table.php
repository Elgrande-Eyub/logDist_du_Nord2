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
        Schema::create('facture_vente_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('factureVente_id');
            $table->foreign('factureVente_id')->references('id')->on('facture_ventes')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('RESTRICT')
            ->onDelete('RESTRICT');;

            $table->unique(['factureVente_id', 'article_id']);

            $table->float('Prix_unitaire',8,2);
            $table->Integer('Quantity');

            $table->float('Total_HT',8,2);
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
        Schema::dropIfExists('facture_vente_articles');
    }
};
