<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Console\Command;

class TestVariations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:variations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test product variations functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Product Variations...');
        $this->newLine();

        // Test products with variations
        $productsWithVariations = Product::whereHas('variations')->with('activeVariations')->take(3)->get();

        foreach ($productsWithVariations as $product) {
            $this->line("Product: {$product->name}");
            $this->line("Has variations: " . ($product->hasVariations() ? 'Yes' : 'No'));
            $this->line("Price range: {$product->price_range}");
            $this->line("Min price: $" . number_format($product->min_price, 2));
            $this->line("Max price: $" . number_format($product->max_price, 2));
            $this->line("Total stock: {$product->total_stock}");
            $this->line("Variations count: {$product->activeVariations->count()}");

            $this->newLine();
            $this->line("Variations:");
            foreach ($product->activeVariations as $variation) {
                $this->line("  - {$variation->display_name}: $" . number_format($variation->effective_price, 2) . " (Stock: {$variation->stock_quantity})");
            }

            $this->newLine(2);
        }

        // Test products without variations
        $productWithoutVariations = Product::whereDoesntHave('variations')->first();
        if ($productWithoutVariations) {
            $this->line("Product without variations: {$productWithoutVariations->name}");
            $this->line("Has variations: " . ($productWithoutVariations->hasVariations() ? 'Yes' : 'No'));
            $this->line("Price range: {$productWithoutVariations->price_range}");
            $this->line("Total stock: {$productWithoutVariations->total_stock}");
        }

        $this->newLine();
        $this->info('Test completed!');
    }
}
