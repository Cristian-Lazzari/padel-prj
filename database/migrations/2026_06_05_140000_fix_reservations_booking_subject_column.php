<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE reservations MODIFY booking_subject BIGINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE reservations MODIFY booking_subject TINYINT NOT NULL');
    }
};
