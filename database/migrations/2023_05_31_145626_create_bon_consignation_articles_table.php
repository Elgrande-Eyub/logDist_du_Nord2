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
        Schema::create('bon_consignation_articles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bonConsignation_id')->unique();
            $table->foreign('bonConsignation_id')->references('id')->on('bon_consignations')->onDelete('cascade');
            $table->string('reference');
            $table->string('article_libelle');
            $table->float('Prix_unitaire',8,2);
            $table->Integer('Quantity');
            $table->float('Total',8,2);

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
        Schema::dropIfExists('bon_consignation_articles');
    }
};
