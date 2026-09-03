<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I campi impegnati dal torneo, come elenco di chiavi di field_set
     * (le stesse usate dalle prenotazioni: "Campo 1", "Campo 2"...).
     * Servono a distinguere due tornei nelle stesse date: si accavallano
     * solo se condividono almeno un campo.
     */
    public function up()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->json('fields')->nullable()->after('location');
        });
    }

    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('fields');
        });
    }
};
