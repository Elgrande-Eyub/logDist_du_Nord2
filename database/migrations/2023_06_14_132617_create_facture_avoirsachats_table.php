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
        Schema::create('facture_avoirsachats', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('avoirsAchat_id');
            $table->foreign('avoirsAchat_id')->references('id')->on('avoirs_achats')->onDelete('restrict');

            $table->unsignedBigInteger('factureAchat_id');
            $table->foreign('factureAchat_id')->references('id')->on('factures')->onDelete('RESTRICT');

            $table->unique(['avoirsAchat_id', 'factureAchat_id']);

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
        Schema::dropIfExists('facture_avoirsachats');
    }
};
