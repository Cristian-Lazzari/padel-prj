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
        Schema::table('players', function (Blueprint $table) {
            // Foto profilo (path relativo sul disco "public")
            $table->string('img')->nullable()->after('surname');

            // Verifica mail tramite OTP
            $table->timestamp('mail_verified_at')->nullable()->after('otp_expires_at');
            $table->timestamp('otp_sent_at')->nullable()->after('mail_verified_at');

            // Dati sportivi / anagrafici aggiuntivi
            $table->string('hand', 2)->nullable()->after('level');              // dx | sx
            $table->string('preferred_position', 20)->nullable()->after('hand'); // dritto | rovescio | indifferente
            $table->string('city', 60)->nullable()->after('preferred_position');
            $table->text('bio')->nullable()->after('city');

            // Scadenza certificato medico
            $table->date('certificate_expires_at')->nullable()->after('certificate');
        });

        // I giocatori già esistenti sono considerati verificati: la verifica OTP
        // vale solo per le registrazioni successive a questa migration, così
        // il blocco prenotazioni non li esclude retroattivamente.
        DB::table('players')->whereNull('mail_verified_at')->update([
            'mail_verified_at' => DB::raw('created_at'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'img',
                'mail_verified_at',
                'otp_sent_at',
                'hand',
                'preferred_position',
                'city',
                'bio',
                'certificate_expires_at',
            ]);
        });
    }
};
