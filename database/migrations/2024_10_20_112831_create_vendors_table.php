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

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('user_id')->constrained();
            $table->enum('account_type', ['Individual', 'Company']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('years_experience');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->boolean('has_crew')->default(false);
            $table->boolean('has_business_insurance')->default(false);
            $table->json('crew_members')->nullable();
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
        Schema::dropIfExists('vendors');
    }
};
