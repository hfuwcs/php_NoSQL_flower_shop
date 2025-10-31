<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class UpdateExistingProductsWithPrice extends Command
{
    protected $signature = 'app:update-products-price';

    protected $description = 'Updates existing products in the database that do not have a price field.';

    public function handle()
    {
        $this->info('Starting to update products with missing prices...');

        $productsToUpdate = Product::whereNull('price')->orWhere('price', '=', null)->get();

        if ($productsToUpdate->isEmpty()) {
            $this->info('No products found with missing prices. Nothing to do.');
            return 0;
        }

        $this->info($productsToUpdate->count() . ' product(s) will be updated.');

        $progressBar = $this->output->createProgressBar($productsToUpdate->count());
        $progressBar->start();

        foreach ($productsToUpdate as $product) {
            $product->price = fake()->randomFloat(2, 10, 999);
            $product->save();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nSuccessfully updated all products with missing prices.");
        return 0;
    }
}