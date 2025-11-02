<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;

class MigrateReviewsToObjectId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:migrate-to-objectid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing reviews to use ObjectId for product_id and user_id, and ensure rating is integer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of reviews...');

        $reviews = Review::all();
        $updated = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($reviews->count());
        $progressBar->start();

        foreach ($reviews as $review) {
            $needsUpdate = false;
            $updates = [];

            // Check product_id
            if (is_string($review->getRawOriginal('product_id'))) {
                $updates['product_id'] = new ObjectId($review->getRawOriginal('product_id'));
                $needsUpdate = true;
            }

            // Check user_id
            if (is_string($review->getRawOriginal('user_id'))) {
                $updates['user_id'] = new ObjectId($review->getRawOriginal('user_id'));
                $needsUpdate = true;
            }

            // Check rating - ensure it's integer
            $rating = $review->getRawOriginal('rating');
            if (is_string($rating)) {
                $updates['rating'] = (int) $rating;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                Review::where('_id', $review->id)->update($updates);
                $updated++;
            } else {
                $skipped++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->info("Updated: {$updated} reviews");
        $this->info("Skipped: {$skipped} reviews (already correct format)");

        return Command::SUCCESS;
    }
}
