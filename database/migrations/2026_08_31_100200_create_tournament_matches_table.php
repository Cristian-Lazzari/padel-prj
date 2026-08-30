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
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();

            $table->string('round', 40)->nullable();       // "Girone A", "Quarti", "Finale"
            // "group" è una parola riservata SQL: la colonna si chiama group_name
            // per non dover fare quoting in ogni query raw.
            $table->string('group_name', 40)->nullable();
            $table->unsignedSmallInteger('position')->default(0); // ordine dentro al round

            // Aggancio opzionale allo slot campo già prenotato.
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();

            $table->foreignId('team_a_id')->nullable()->constrained('tournament_registrations')->nullOnDelete();
            $table->foreignId('team_b_id')->nullable()->constrained('tournament_registrations')->nullOnDelete();

            // Set giocati: [{"a":6,"b":4},{"a":6,"b":3}]
            $table->json('score')->nullable();
            $table->enum('winner', ['a', 'b', 'draw'])->nullable();

            $table->dateTime('played_at')->nullable();
            $table->enum('status', ['scheduled', 'played', 'cancelled'])->default('scheduled');

            $table->timestamps();

            $table->index(['tournament_id', 'round'], 'tournament_match_round_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_matches');
    }
};
