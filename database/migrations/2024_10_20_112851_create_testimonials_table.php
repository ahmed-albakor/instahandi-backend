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

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->tinyInteger('rating')->unsigned();
            $table->string('client_name', 255);
            $table->string('job', 255);
            $table->string('profile_photo', 110);
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('testimonials');
    }
};
