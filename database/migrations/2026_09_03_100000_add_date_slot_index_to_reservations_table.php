<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * date_slot non aveva indice: ogni schermata che guarda un periodo (il
 * calendario, l'elenco delle prenotazioni, le statistiche) leggeva la tabella
 * intera. È una stringa 'Y-m-d H:i' di 18 caratteri, quindi l'ordine
 * alfabetico coincide con quello cronologico e un indice normale basta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->index('date_slot', 'reservations_date_slot_index');
            // Il calendario chiede sempre un periodo escludendo le annullate
            $table->index(['status', 'date_slot'], 'reservations_status_date_slot_index');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_date_slot_index');
            $table->dropIndex('reservations_status_date_slot_index');
        });
    }
};
