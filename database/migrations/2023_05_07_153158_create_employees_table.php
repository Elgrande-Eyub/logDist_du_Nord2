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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('nom_employee');
            $table->string('code_employee');
            $table->string('CIN_employee');
            $table->string('matricule_employee');
            $table->string('telephone_employee');
            $table->string('email_employee');
            $table->string('adresse_employee');
            $table->Date('date_embauche');

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('employee_roles')->onDelete('restrict');

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
        Schema::dropIfExists('employees');
    }
};
