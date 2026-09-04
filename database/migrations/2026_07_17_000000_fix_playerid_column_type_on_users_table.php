<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Il campo playerId era definito come TINYINT (max 127): appena l'id di un
     * player superava 127 la registrazione andava in overflow.
     * Lo allineiamo al tipo di players.id (BIGINT UNSIGNED).
     *
     * @return void
     */
    public function up()
    {
        // `MODIFY` esiste solo in MySQL: sotto SQLite (i test) non serve.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `users` MODIFY `playerId` BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // `MODIFY` esiste solo in MySQL: sotto SQLite (i test) non serve.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `users` MODIFY `playerId` TINYINT NULL');
    }
};
