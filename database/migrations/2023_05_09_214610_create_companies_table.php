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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->bigInteger('ICE');
            $table->string('IF')->nullable();
            $table->string('RC')->nullable();
            $table->string('adresse')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('fax')->nullable();
            $table->float('capital',12,2)->nullable();

            $table->softDeletes();
            $table->timestamps();

        });
    }


    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
