<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('street_address');
            $table->text('exstra_address')->nullable();
            $table->string('country', 50);
            $table->string('city', 50);
            $table->string('state', 20);
            $table->string('zip_code', 20);
            $table->timestamps(); 
            $table->softDeletes(); 
        });


        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
