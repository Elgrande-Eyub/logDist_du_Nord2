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
        Schema::create('bon_sortie_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bonSorties_id');
            $table->foreign('bonSorties_id')->references('id')->on('bon_sorties')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('RESTRICT')
            ->onDelete('RESTRICT');;

            $table->unique(['bonSorties_id', 'article_id']);
            $table->Integer('QuantitySortie');

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
        Schema::dropIfExists('bon_sortie_articles');
    }
};
