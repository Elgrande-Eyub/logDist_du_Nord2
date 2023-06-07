<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('secteurs', function (Blueprint $table) {
            $table->id();

            $table->string('secteur');
            $table->unsignedBigInteger('warehouseDistrubtion_id');
            $table->foreign('warehouseDistrubtion_id')->references('id')->on('warehouses')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down()
    {
        Schema::dropIfExists('secteurs');
    }
};
