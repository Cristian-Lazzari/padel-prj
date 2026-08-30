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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();

            $table->string('title', 120);
            $table->text('description');

            $table->enum('category', ['racchette', 'abbigliamento', 'scarpe', 'accessori', 'altro'])
                ->default('altro');
            $table->enum('condition', ['nuovo', 'come nuovo', 'usato'])->default('usato');

            $table->decimal('price', 8, 2)->nullable();   // null = trattabile / da concordare

            // Contatti: si mostrano solo se il venditore ha acconsentito.
            $table->string('contact_phone', 25)->nullable();
            $table->string('contact_mail', 120)->nullable();
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_mail')->default(false);

            $table->enum('status', ['pending', 'published', 'sold', 'rejected', 'expired'])
                ->default('pending');
            $table->string('reject_reason')->nullable();

            $table->dateTime('expires_at')->nullable();
            $table->unsignedInteger('views')->default(0);

            $table->timestamps();

            // La bacheca pubblica filtra sempre su questi campi.
            $table->index(['status', 'expires_at'], 'listings_status_expires_index');
            $table->index(['category', 'status'], 'listings_category_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
};
