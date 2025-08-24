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
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('object', 150);
            $table->string('heading', 150);
            $table->text('body');
            $table->text('ending');
            $table->string('sender', 50);
            $table->string('img_1')->nullable();
            $table->string('img_2')->nullable();
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
        Schema::dropIfExists('models');
    }
};
