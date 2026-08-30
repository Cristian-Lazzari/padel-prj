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
        Schema::table('reservations', function (Blueprint $table) {
            // Marca le prenotazioni generate da un campo fisso: servono a
            // distinguerle in calendario e a ripulirle quando lo slot cambia.
            $table->foreignId('fixed_slot_id')->nullable()->after('booking_subject')
                ->constrained('fixed_slots')->nullOnDelete();
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
            $table->dropForeign(['fixed_slot_id']);
            $table->dropColumn('fixed_slot_id');
        });
    }
};
