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
        Schema::create('tournament_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players');

            // Valorizzato solo sui tornei a coppie.
            $table->foreignId('partner_player_id')->nullable()->constrained('players');

            $table->string('team_name', 80)->nullable();

            $table->enum('status', ['pending', 'confirmed', 'waitlist', 'rejected', 'cancelled'])
                ->default('pending');

            $table->boolean('paid')->default(false);  // quota incassata in struttura
            $table->text('note')->nullable();

            $table->timestamps();

            // Un giocatore può iscriversi una sola volta allo stesso torneo.
            $table->unique(['tournament_id', 'player_id'], 'tournament_player_unique');
            $table->index(['tournament_id', 'status'], 'tournament_registration_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_registrations');
    }
};
