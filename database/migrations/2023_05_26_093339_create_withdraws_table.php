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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();

            $table->string('type'); // withdraw , depots
            $table->float('solde',16,2); // solde
            $table->string('mode'); // bank ou espece
            $table->string('motif')->nullable();

            // For Depense Charges

            $table->unsignedBigInteger('depense_id')->nullable();
            $table->foreign('depense_id')->nullable()->references('id')->on('depenses')->onDelete('restrict');
            $table->string('attachement')->nullable();

            // For Depense Charges

            $table->unsignedBigInteger('journal_id')->nullable();
            $table->foreign('journal_id')->nullable()->references('id')->on('journals')->onDelete('restrict');

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
        Schema::dropIfExists('withdraws');
    }
};
