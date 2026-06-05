<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'booking_subject')) {
            return;
        }

        if (DB::table('reservations')->where('booking_subject', '<', 0)->exists()) {
            throw new RuntimeException('Cannot convert reservations.booking_subject to unsigned: negative values exist.');
        }

        DB::statement('ALTER TABLE reservations MODIFY booking_subject BIGINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'booking_subject')) {
            return;
        }

        if (DB::table('reservations')->where('booking_subject', '>', 127)->exists()) {
            throw new RuntimeException('Cannot rollback reservations.booking_subject to tinyint: values greater than 127 exist.');
        }

        DB::statement('ALTER TABLE reservations MODIFY booking_subject TINYINT NOT NULL');
    }
};
