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
        Schema::create('avoirs_vente_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avoirsVente_id');
            $table->foreign('avoirsVente_id')->references('id')->on('avoirs_ventes')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('RESTRICT')
            ->onDelete('RESTRICT');

            $table->unique(['avoirsVente_id', 'article_id']);

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
        Schema::dropIfExists('avoirs_vente_articles');
    }
};
