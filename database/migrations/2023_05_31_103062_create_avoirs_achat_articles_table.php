<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
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
        Schema::create('avoirs_achat_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('avoirsAchat_id');
            $table->foreign('avoirsAchat_id')->references('id')->on('avoirs_achats')->onDelete('cascade');

            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('RESTRICT')
            ->onDelete('RESTRICT');;

            $table->unique(['avoirsAchat_id', 'article_id']);

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
        Schema::dropIfExists('avoirs_achat_articles');
    }
};
