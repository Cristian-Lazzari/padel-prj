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
            $table->string('name', 50);                            
            $table->string('surname', 50);
            $table->string('phone', 14);
            $table->string('mail', 50);
            $table->tinyInteger('level')->default(1);

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
