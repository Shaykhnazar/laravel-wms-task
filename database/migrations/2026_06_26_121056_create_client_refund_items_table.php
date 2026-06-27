<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_refund_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('refunded_at');
            $table->timestamps();

            $table->index('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_refund_items');
    }
};
