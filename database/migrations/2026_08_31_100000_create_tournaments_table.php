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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();

            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->text('regulation')->nullable();      // regolamento mostrato nel dettaglio
            $table->string('cover')->nullable();          // path sul disco "public"

            $table->string('type', 40)->default('Padel'); // padel, calcio, basket...
            $table->enum('format', ['gironi', 'eliminazione', 'americano'])->default('gironi');

            $table->unsignedTinyInteger('level_min')->nullable();
            $table->unsignedTinyInteger('level_max')->nullable();

            $table->unsignedSmallInteger('teams_max')->default(8);
            $table->boolean('is_pair')->default(true);    // true = coppie, false = singolo

            $table->decimal('price', 8, 2)->nullable();   // pagamento in struttura, nessun online

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('registration_opens_at')->nullable();
            $table->dateTime('registration_closes_at')->nullable();

            $table->enum('status', ['draft', 'open', 'closed', 'running', 'finished', 'cancelled'])
                ->default('draft');

            $table->string('location')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // La vetrina pubblica filtra sempre su stato e data di inizio.
            $table->index(['status', 'starts_at'], 'tournaments_status_start_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments');
    }
};
