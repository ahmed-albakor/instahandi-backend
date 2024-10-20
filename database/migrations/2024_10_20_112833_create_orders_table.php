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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('service_request_id')->constrained('service_requests');
            $table->foreignId('proposal_id')->nullable()->constrained('proposals');
            $table->enum('status', ["pending", "execute", "completed", "canceled"]);
            $table->string('title', 255);
            $table->text('description');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->decimal('price', 10, 2);
            $table->enum('payment_type', ["flat_rate", "hourly_rate"]);
            $table->integer('works_hours')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('completion_date')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
