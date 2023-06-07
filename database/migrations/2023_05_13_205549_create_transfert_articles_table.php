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
        Schema::create('transfert_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('transfert_id');
            $table->foreign('transfert_id')->references('id')->on('transferts')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('RESTRICT')
            ->onDelete('RESTRICT');;

            $table->unique(['transfert_id', 'article_id']);

            $table->Integer('Quantity');

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
        Schema::dropIfExists('transfert_articles');
    }
};
