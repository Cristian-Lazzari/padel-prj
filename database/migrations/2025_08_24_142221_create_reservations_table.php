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
    public function up(){
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('date_slot', 18);
            $table->string('field'); // 1, 2, 3 
            $table->string('status'); // 1 confirmed, 2 cancelled, 3 noshow
            $table->string('message')->nullable(); // messaggio opzionale
            $table->text('dinner'); //[ status, ospiti, orario]

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};
