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
        Schema::table('bon_livraisons', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('bonretourAchat_id')->nullable();

            $table->foreign('bonretourAchat_id')
                ->references('id')
                ->on('bonretour_achats')
                ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bon_livraisons', function (Blueprint $table) {
            //
        });
    }
};
