<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_price', 10);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_remaining');
            $table->timestamps();

            $table->index(['product_id', 'quantity_remaining']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_items');
    }
};
