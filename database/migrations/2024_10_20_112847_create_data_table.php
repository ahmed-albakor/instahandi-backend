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

        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255);
            $table->text('value')->nullable();
            $table->enum('type', ["int", "float", "text", "long_text", "list", "map", "image", "file", "bool"]);
            $table->boolean('allow_null')->default(false);
            $table->timestamps();
        });


        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};
