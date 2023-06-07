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
        Schema::create('facture_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('facture_id');
            $table->foreign('facture_id')->references('id')->on('factures')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('restrict');

            $table->unique(['facture_id', 'article_id']);
            $table->float('Prix_unitaire',8,2);
            $table->Integer('Quantity');
            $table->float('Total_HT',8,2);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facture_articles');
    }
};
