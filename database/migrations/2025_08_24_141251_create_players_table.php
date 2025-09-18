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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('player');                            
            $table->string('name', 50);                            
            $table->string('surname', 50);
            $table->string('nickname', 50)->unique();
            $table->string('sex', 1);
            $table->string('phone', 25);
            $table->string('mail', 50);
            $table->tinyInteger('level')->default(1);
            
            $table->string('otp')->nullable();
            $table->string('otp_expires_at')->nullable();
            
            $table->text('note')->nullable();
            
            $table->string('certificate')->nullable();    
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
        Schema::dropIfExists('players');
    }
};
