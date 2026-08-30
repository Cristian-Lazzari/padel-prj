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
        // Rete di sicurezza: le partite aperte identificano l'owner tramite
        // reservations.booking_subject, che nello schema originale è tinyint
        // e va in overflow oltre i 127 giocatori. La conversione è la stessa
        // della migration 2026_06_05_140000 (che gira prima di questa): qui
        // viene ripetuta in forma idempotente perché la feature dipende da
        // questo campo e non deve rompersi se quella non è stata applicata.
        $this->ensureBookingSubjectIsBigint();

        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('is_open')->default(false)->after('lesson');
            $table->enum('open_category', ['match', 'lesson', 'tournament'])->nullable()->after('is_open');
            $table->unsignedTinyInteger('slots_total')->nullable()->after('open_category');
            $table->unsignedTinyInteger('level_min')->nullable()->after('slots_total');
            $table->unsignedTinyInteger('level_max')->nullable()->after('level_min');
            $table->text('open_note')->nullable()->after('level_max');

            // Termine ultimo per iscriversi/disiscriversi. Non è possibile
            // usare un default SQL calcolato da date_slot: viene valorizzato
            // all'apertura con l'inizio dello slot.
            $table->dateTime('open_closes_at')->nullable()->after('open_note');

            // La lista pubblica filtra sempre su questi due campi.
            $table->index(['is_open', 'status'], 'reservations_open_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_open_status_index');
            $table->dropColumn([
                'is_open',
                'open_category',
                'slots_total',
                'level_min',
                'level_max',
                'open_note',
                'open_closes_at',
            ]);
        });
    }

    private function ensureBookingSubjectIsBigint(): void
    {
        if (! Schema::hasColumn('reservations', 'booking_subject')) {
            return;
        }

        // SHOW COLUMNS non accetta binding: il nome è una costante del codice.
        $column = collect(DB::select("SHOW COLUMNS FROM reservations LIKE 'booking_subject'"))->first();

        if (! $column || str_contains(strtolower($column->Type), 'bigint')) {
            return;
        }

        if (DB::table('reservations')->where('booking_subject', '<', 0)->exists()) {
            throw new RuntimeException('Impossibile convertire reservations.booking_subject: esistono valori negativi.');
        }

        DB::statement('ALTER TABLE reservations MODIFY booking_subject BIGINT UNSIGNED NOT NULL');
    }
};
