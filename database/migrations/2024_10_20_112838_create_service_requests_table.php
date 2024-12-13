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

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('client_id')->constrained('clients');
            $table->string('title', 255);
            $table->text('description');
            $table->enum('status', ['pending', 'accepted', 'completed', 'rejected', 'canceled']);
            $table->enum('payment_type', ['flat_rate', 'hourly_rate']);
            $table->string('estimated_hours', 50)->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->foreignId('service_id')->constrained('services');
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
        Schema::dropIfExists('service_requests');
    }
};
