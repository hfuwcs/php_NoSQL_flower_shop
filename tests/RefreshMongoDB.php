<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

trait RefreshMongoDB
{
    /**
     * Refresh the MongoDB database before each test.
     */
    protected function refreshMongoDB(): void
    {
        $collections = [
            'users',
            'products',
            'orders',
            'order_items',
            'reviews',
            'rewards',
            'user_rewards',
            'coupons',
            'point_transactions',
            'carts',
            'membership_tiers',
        ];

        $connection = DB::connection('mongodb');
        $database = $connection->getMongoDB();

        foreach ($collections as $collection) {
            try {
                $database->dropCollection($collection);
            } catch (\Exception $e) {
                // Collection may not exist, ignore
            }
        }
    }

    /**
     * Boot the trait by registering the beforeEach callback.
     */
    protected function setUpRefreshMongoDB(): void
    {
        $this->refreshMongoDB();
    }
}
