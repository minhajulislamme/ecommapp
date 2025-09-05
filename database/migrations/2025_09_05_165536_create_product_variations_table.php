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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('display_name'); // e.g., "Size", "Color", "Material"
            $table->json('attribute_values'); // e.g., {"size": "Large", "color": "Red"}
            $table->decimal('price', 10, 2); // Price for this variation
            $table->decimal('sale_price', 10, 2)->nullable(); // Sale price for this variation
            $table->string('sku')->unique(); // Unique SKU for this variation
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
