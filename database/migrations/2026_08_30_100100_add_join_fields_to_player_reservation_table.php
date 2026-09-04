<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        // Il pivot può contenere doppioni storici: vanno ripuliti prima
        // di poter aggiungere l'indice unico.
        $this->removeDuplicates();

        Schema::table('player_reservation', function (Blueprint $table) {
            // Default "accepted": così le sync() già in uso nel back office
            // continuano a creare iscrizioni valide senza modifiche.
            $table->enum('join_status', ['pending', 'accepted', 'rejected', 'cancelled'])
                ->default('accepted')
                ->after('reservation_id');
            $table->timestamp('joined_at')->nullable()->after('join_status');
            $table->boolean('is_owner')->default(false)->after('joined_at');

            $table->unique(['player_id', 'reservation_id'], 'player_reservation_unique');
            $table->index(['reservation_id', 'join_status'], 'player_reservation_status_index');
        });

        // Le iscrizioni preesistenti sono confermate a tutti gli effetti.
        DB::table('player_reservation')->update([
            'join_status' => 'accepted',
            'joined_at' => now(),
        ]);

        // Chi ha prenotato ed è anche in squadra viene marcato come owner.
        // Sottoquery invece di UPDATE ... JOIN: la stessa istruzione gira
        // su MySQL e su SQLite (i test).
        DB::statement('
            UPDATE player_reservation
            SET is_owner = 1
            WHERE player_id = (
                SELECT booking_subject FROM reservations
                WHERE reservations.id = player_reservation.reservation_id
            )
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_reservation', function (Blueprint $table) {
            $table->dropUnique('player_reservation_unique');
            $table->dropIndex('player_reservation_status_index');
            $table->dropColumn(['join_status', 'joined_at', 'is_owner']);
        });
    }

    private function removeDuplicates(): void
    {
        $duplicates = DB::table('player_reservation')
            ->select('player_id', 'reservation_id', DB::raw('COUNT(*) as total'))
            ->groupBy('player_id', 'reservation_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $row) {
            DB::table('player_reservation')
                ->where('player_id', $row->player_id)
                ->where('reservation_id', $row->reservation_id)
                ->delete();

            DB::table('player_reservation')->insert([
                'player_id' => $row->player_id,
                'reservation_id' => $row->reservation_id,
            ]);
        }
    }
};
