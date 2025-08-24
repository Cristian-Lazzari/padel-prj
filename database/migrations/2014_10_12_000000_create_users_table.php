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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('client');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            
            $table->tinyInteger('role_agency');                  //0 owner, 1 employee, 2 smm
            $table->string('name', 50)->nullable();                            //dati personali
            $table->string('surname', 50)->nullable();
            $table->string('phone', 15)->nullable();  
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
