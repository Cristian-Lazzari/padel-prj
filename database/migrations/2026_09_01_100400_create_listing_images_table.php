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
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('path');                        // disco "public"
            // Ordine in galleria: 0 è la copertina. Massimo 4 per annuncio,
            // limite applicato lato applicazione.
            $table->unsignedTinyInteger('position')->default(0);

            $table->timestamps();

            $table->index(['listing_id', 'position'], 'listing_images_order_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listing_images');
    }
};
