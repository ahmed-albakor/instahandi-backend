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

        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('order_id')->constrained();
            $table->decimal('amount', 8, 2)->default(0);
            $table->enum('method', ['stripe']);
            $table->enum('status', ['pending', 'confirm', 'return', 'cancel']);
            $table->text('description')->nullable();
            $table->json('payment_data');
            $table->foreignId('user_id')->constrained();
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
        Schema::dropIfExists('vendors_payments');
    }
};
