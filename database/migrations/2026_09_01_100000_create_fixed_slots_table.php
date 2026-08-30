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
        Schema::create('fixed_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')->constrained('players');

            $table->string('field');                       // stessa chiave di reservations.field
            // Convenzione Carbon::dayOfWeek: 0 = domenica ... 6 = sabato.
            $table->unsignedTinyInteger('weekday');
            $table->string('start_time', 5);               // "18:30"
            $table->unsignedTinyInteger('duration')->default(3); // n * m_during del campo

            $table->date('valid_from');
            $table->date('valid_to')->nullable();          // null = a tempo indeterminato

            $table->enum('status', ['active', 'suspended', 'ended'])->default('active');

            $table->decimal('price', 8, 2)->nullable();    // si salda in struttura
            $table->text('note')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'weekday'], 'fixed_slots_status_weekday_index');
            $table->index(['field', 'weekday'], 'fixed_slots_field_weekday_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_slots');
    }
};
