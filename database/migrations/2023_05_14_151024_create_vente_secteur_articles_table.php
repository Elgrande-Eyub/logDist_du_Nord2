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
        Schema::create('vente_secteur_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('venteSecteur_id');
            $table->foreign('venteSecteur_id')->references('id')->on('vente_secteurs')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('RESTRICT')
            ->onDelete('RESTRICT');;

            $table->unique(['venteSecteur_id', 'article_id']);
            $table->Integer('qte_sortie');
            $table->Integer('qte_retourV');
            $table->Integer('qte_perime');
            $table->Integer('qte_echange');
            $table->Integer('qte_gratuit');
            $table->Integer('qte_credit');
            $table->Integer('qte_vendu');
            $table->float('Prix_unitaire',8,2);
            $table->float('Total_Vendu',8,2)->nullable();

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
        Schema::dropIfExists('vente_secteur_articles');
    }
};
