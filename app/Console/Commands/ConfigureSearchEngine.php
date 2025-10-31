<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use MeiliSearch\Client;

class ConfigureSearchEngine extends Command
{
    protected $signature = 'app:configure-search';
    protected $description = 'Configures the Meilisearch indexes with filterable and sortable attributes.';

    public function handle()
    {
        $this->info('Configuring Meilisearch index for Products...');

        // Tạo MeiliSearch client từ config
        $meili = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );

        $indexName = (new Product())->searchableAs();

        // Update filterable attributes
        $meili->index($indexName)->updateFilterableAttributes([
            'category',
            'price',
        ]);

        // Update sortable attributes (optional)
        $meili->index($indexName)->updateSortableAttributes([
            'price',
            'created_at',
        ]);

        $this->info("Index '{$indexName}' configured successfully.");
        $this->info("Filterable attributes: category, price");
        $this->info("Sortable attributes: price, created_at");
        
        return 0;
    }
}