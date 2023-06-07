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
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            $table->unsignedBigInteger('from');
            $table->foreign('from')->references('id')->on('warehouses')->onDelete('restrict');

            $table->unsignedBigInteger('to');
            $table->foreign('to')->references('id')->on('warehouses')->onDelete('restrict');

            $table->unsignedBigInteger('camion_id')->nullable();
            $table->foreign('camion_id')->references('id')->on('camions')->onDelete('restrict');

            $table->unsignedBigInteger('transporteur_id')->nullable();
            $table->foreign('transporteur_id')->references('id')->on('employees')->onDelete('restrict')->onUpdate('restrict');

            $table->dateTime('dateTransfert')->default(Carbon::now());
            $table->boolean('Confirme')->default(false);
            $table->string('Commentaire')->nullable();

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
        Schema::dropIfExists('transferts');
    }
};
