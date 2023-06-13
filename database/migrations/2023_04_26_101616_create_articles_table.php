<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            $table->string('article_libelle');
            $table->string('reference');
            $table->float('prix_unitaire',8,2);
            $table->float('prix_public',5,2);
            $table->float('prix_achat',5,2)->nullable();
            $table->float('client_Fedele',5,2)->nullable();
            $table->float('demi_grossiste',5,2)->nullable();
            $table->string('unite');
            $table->integer('alert_stock')->default(0);

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->nullable()->references('id')->on('article_categories')->onDelete('restrict');

            $table->unsignedBigInteger('fournisseur_id');
            $table->foreign('fournisseur_id')->nullable()->references('id')->on('fournisseurs')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
