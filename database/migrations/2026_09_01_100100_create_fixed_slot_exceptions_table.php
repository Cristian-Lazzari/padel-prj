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
        Schema::create('fixed_slot_exceptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixed_slot_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('reason', ['festivo', 'sospensione', 'recupero'])->default('sospensione');
            $table->string('note')->nullable();

            $table->timestamps();

            // Una sola eccezione per data su ciascun campo fisso.
            $table->unique(['fixed_slot_id', 'date'], 'fixed_slot_exception_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_slot_exceptions');
    }
};
